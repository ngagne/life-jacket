<?php

namespace ATC;

/**
 * Class Controller
 * @package ATC
 */
class Controller {
    protected $router;
    protected $view;
    protected $layout;
    protected $template;
    protected $errorMessages = '';

    /**
     * Controller constructor.
     *
     * @param Router $router
     * @param View $view
     */
    public function __construct(\ATC\Router $router, \ATC\View $view) {
        $this->router = $router;
        $this->view = $view;
    }

    /**
     * Handles requested "/" route
     */
    public function indexAction() {
        // handle contact form
        if (!empty($_POST['form_submit'])) {
            $formValidator = new FormValidator($_POST['form'], $this->view);

            // if there were no errors
            if ($formValidator->isValid()) {
                // format user submitted data
                $data = array();
                $data[] = '<table>';
                foreach ($_POST['form'] as $field => $value) {
                    $data[] = '<tr><th>' . ucwords(str_replace(array('-', '_'), ' ', $field)) . '</th><td>' . nl2br($value) . '</td></tr>';
                }
                $data[] = '</table>';

                // send message
                $mailer = new Mailer();
                $result = $mailer->sendSystemMessage(implode("\n", $data), $_POST['form_submit']);

                if ($result) {
                    $this->router->redirect('/thanks');
                    die();
                }
            } else {
                $this->errorMessages = $formValidator->getFormattedErrors();
            }
        }
    }

    /**
     * Handles requested "/admin/" route
     */
    public function adminAction() {
        $config = Config::getInstance();
        $session = Session::getInstance();

        // simple authentication
        if (!$this->isLoggedIn()) {
            $this->router->redirect('/admin-login');
        }


        $stringsHandler = new StringsHandler();
        $stringsHandler->rebuildStrings();
        $strings = $stringsHandler->strings;

        // handle form submission
        if (!empty($_POST['data']) && $this->isCsrfValid()) {
            $newStrings = array_replace_recursive($strings, $_POST['data']);

            // save any upload images
            if (!empty($_FILES) && isset($_FILES['data'])) {
                foreach ($_FILES['data']['name'] as $group => $node) {
                    foreach ($node as $token => $item) {
                        $source = $_FILES['data']['tmp_name'][$group][$token];
                        if (is_uploaded_file($source)) {
                            $ext = pathinfo($_FILES['data']['name'][$group][$token], PATHINFO_EXTENSION);
                            $file = $group . '~' . str_replace('img/', '', $token) . (!empty($ext) ? ('.' . $ext) : '');
                            $destination = PUBLIC_PATH . '/' . trim($config->image_uploads_path, '/') . '/' . str_replace('/', '~~', $file);
                            if (!move_uploaded_file($source, $destination)) {
                                throw new \Exception('Image was not able to be uploaded (directory may not be writable): ' . $destination);
                            }

                            if (!isset($newStrings[$group])) {
                                $newStrings[$group] = array();
                            }
                            $newStrings[$group][$token] = $file;
                        }
                    }
                }
            }

            // save data
            $stringsHandler->setStrings($newStrings);
            $config->updateSettings($_POST['settings']);

            // clear cached pages
            if ($config->cache_enabled) {
                $cache = Cache::getInstance();
                $cache->clear();
            }

            // redirect back to same page
            $this->router->redirect('/admin');
        }

        $templates = array(
            'navItem' => file_get_contents(APPLICATION_PATH . '/layouts/_system/admin-nav-item.html'),
            'fieldset' => file_get_contents(APPLICATION_PATH . '/layouts/_system/admin-fieldset.html'),
            'section' =>file_get_contents(APPLICATION_PATH . '/layouts/_system/admin-section.html'),
            'row' =>file_get_contents(APPLICATION_PATH . '/layouts/_system/admin-row.html'),
        );

        $settings = array(
            'General' => array(
                'site_name' => $config->site_name,
            ),
            'Analytics' => array(
                'google_analytics_id'   => $config->google_analytics_id,
            ),
            'Recaptcha' => array(
                'recaptcha_site_key'    => $config->recaptcha_site_key,
                'recaptcha_secret_key'  => $config->recaptcha_secret_key,
            ),
            'Mail' => array(
                'system_mail_from_name'     => $config->system_mail_from_name,
                'system_mail_from_email'    => $config->system_mail_from_email,
                'system_mail_to'            => $config->system_mail_to,
                'system_mail_subject'       => $config->system_mail_subject,
            ),
        );

        $html['nav_items'] = array();
        foreach (array_keys($strings) as $key) {
            $tokens = array(
                '[[__name]]' => $key,
                '[[__id]]' => str_replace('/', '-', str_replace('-', '--', $key)),
            );
            $html['nav_items'][] = str_replace(array_keys($tokens), $tokens, $templates['navItem']);
        }


        $html['page'] = $this->renderSettingGroups($strings, $templates);
        $html['settings'] = $this->renderSettingGroups($settings, $templates, 'settings');


        $this->view->replaceToken('__settings', implode("\n", $html['settings']));
        $this->view->replaceToken('__page_strings', implode("\n", $html['page']));
        $this->view->replaceToken('__nav_items', implode("\n", $html['nav_items']));
    }

    /**
     * Handles requested "/admin-login/" route
     */
    public function adminLoginAction() {
        $config = Config::getInstance();

        if (!empty($_POST['form'])) {
            $username = !empty($_POST['form']['username']) ? $_POST['form']['username'] : '';
            $password = !empty($_POST['form']['password']) ? $_POST['form']['password'] : '';

            $session = Session::getInstance();

            if ($username == $config->admin_username || $password == $config->admin_password) {
                $session->set('is_logged_in', true);

                // generate and store CSRF token
                $csrf = md5(uniqid(rand()));
                $session->set('csrf', $csrf);

                $this->router->redirect('/admin');
            }

            $alertTemplate = file_get_contents(APPLICATION_PATH . '/layouts/_system/alert.html');
            $rowTemplate = file_get_contents(APPLICATION_PATH . '/layouts/_system/alert-row.html');
            $this->errorMessages = str_replace('[[__rows]]', str_replace('[[__message]]', 'The credentials you entered are not correct.', $rowTemplate), $alertTemplate);
        }
    }

    /**
     * Handles requested "/admin-logout/" route
     */
    public function adminLogoutAction() {
        $session = Session::getInstance();
        $session->set('is_logged_in', false);
        $this->router->redirect('/admin');
    }

    /**
     * Check for valid CSRF token
     *
     * @return bool
     */
    protected function isCsrfValid() {
        $session = Session::getInstance();
        $csrf = $session->get('csrf', '');

        return $csrf == isset($_POST['csrf']) ? $_POST['csrf'] : '';
    }

    /**
     * Check if admin user is logged in
     *
     * @return bool
     */
    protected function isLoggedIn() {
        $session = Session::getInstance();
        return (bool) $session->get('is_logged_in', false);
    }

    /**
     * Render groups of admin input fields
     *
     * @param array $data
     * @param array $templates
     * @param string $fieldGroup
     * @return array
     */
    protected function renderSettingGroups(Array $data, Array $templates, $fieldGroup = '') {
        $config = Config::getInstance();
        $fieldsetTemplate = $templates[!$fieldGroup ? 'section' : 'fieldset'];
        $rowTemplate = $templates['row'];

        $html = array();
        foreach ($data as $group => $items) {
            if (!count($items)) {
                continue;
            }

            $rows = array();
            foreach ($items as $key => $value) {
                // detect special suffixes
                $suffix = $fieldGroup == '' ? 'textarea' : 'text';
                $suffixOffset = strpos($key, '/');
                if ($suffixOffset !== false) {
                    $suffix = substr($key, $suffixOffset + 1);
                }

                // format values
                $name = $fieldGroup == '' ? "data[$group][$key]" : "{$fieldGroup}[$key]";
                $label = ucwords(preg_replace('/\/.+/', '', str_replace(array('-', '_'), ' ', $key)));

                // determine helper class to use for rendering form field
                $helperClass = __NAMESPACE__ . '\TokenHelpers\\' . Utilities::formatClassName($suffix);
                $helper = new $helperClass($value, $name, $label);

                // add field to row
                $rows[] = str_replace('[[__content]]', $helper->getField(), $rowTemplate);
            }

            $tokens = array(
                '[[__id]]' => str_replace('/', '-', str_replace('-', '--', $group)),
                '[[__rows]]' => implode("\n", $rows),
                '[[__legend]]' => $group,
            );

            $html[] = str_replace(array_keys($tokens), $tokens, $fieldsetTemplate);
        }

        return $html;
    }

    /**
     * Pre-process HTML
     */
    public function preProcess() {
        $config = Config::getInstance();

        // generate HTML <head> elements
        $htmlHead = array();

        // determine page title
        $title = $this->view->getTokenValue('title');
        if ($title == '') {
            $title = $this->router->action != 'index' ? ucwords(str_replace(array('-', '_'), ' ', $this->router->action)) : '';
        }
        $htmlHead['title'] = '<title>' . (!empty($title) ? ($title . ' | ' . $config->site_name) : $config->site_name) . '</title>';

        // generate meta description (if available)
        $metaDesc = $this->view->getTokenValue('meta_desc');
        if ($metaDesc != '') {
            $htmlHead['meta_desc'] = '<meta name="description" content="' . htmlspecialchars($metaDesc, ENT_QUOTES) . '">';
        }

        // process dynamic tokens
        $this->view->replaceToken('__head', implode("\n", $htmlHead));
        $this->view->replaceToken('__year', date('Y'));
        $this->view->replaceToken('__recaptcha_sitekey', $config->recaptcha_site_key);
        $this->view->replaceToken('__google_analytics_id', $config->google_analytics_id);
        $this->view->replaceToken('__site_name', $config->site_name);
    }

    /**
     * Final processing and output of HTML
     */
    public function render() {
        // process dynamic tokens
        $this->view->replaceToken('__errors', $this->errorMessages);

        $config = Config::getInstance();
        $html = $this->view->render();

        // handle CSRF token
        if (strpos($html, '[[__csrf]]') !== false) {
            $session = Session::getInstance();
            $csrf = $session->get('csrf', '');
            $html = str_replace('[[__csrf]]', $csrf, $html);
        }

        // handle extra data loaded before </body>
        $htmlBeforeBodyClose = array();
        if (!empty($_POST['form'])) {
            // sanitize form data
            $formData = array_map(array($this, 'sanitizeArray'), $_POST['form']);
            $htmlBeforeBodyClose[] = '<script>document.addEventListener("DOMContentLoaded",function(){populateForm(' . json_encode($formData) . ')});</script>';
        }
        $html = str_replace('</body>', implode("\n", $htmlBeforeBodyClose) . "\n" . '</body>', $html);

        // cache output
        if ($config->cache_enabled && $_SERVER['REQUEST_METHOD'] == 'GET') {
            $cache = Cache::getInstance();
            $cache->store('controller:render:' . $this->router->reqURI, $html);
        }

        die($html);
    }

    protected function sanitizeArray($n) {
        return htmlspecialchars($n, ENT_QUOTES, 'UTF-8');
    }
}