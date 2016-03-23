<?php

namespace ATC;

/**
 * Class FormValidator
 * @package ATC
 */
class FormValidator
{
    protected $errors;
    protected $data;
    protected $fields;
    protected $view;

    /**
     * FormValidator constructor.
     * @param array $post
     * @param View $view
     */
    public function __construct(Array $post, View $view) {
        $this->view = $view;

        $errors = array();
        $fields = $this->view->getFormFields();
        $formData = array();
        foreach ($fields as $name => $field) {
            // filter values
            $value = !empty($post[$name]) ? trim($post[$name]) : '';
            $formattedName = Utilities::formatClassName($name);

            // validate data
            if (isset($field['required']) && $field['required'] != 'false' && empty($value)) {
                $errors[$name] = $formattedName . ' is a required field.';
            }
            if (!empty($value)) {
                if (isset($field['type']) && $field['type'] == 'email' && !$this->isValidEmail($value)) {
                    $errors[$name] = $formattedName . ' must be a valid email address.';
                }
                if (isset($field['type']) && $field['type'] == 'url' && !preg_match('@^https?://@', $value)) {
                    $errors[$name] = $formattedName . ' must be a valid URL.';
                }
                if (isset($field['maxlength']) && $field['maxlength'] < trim(strlen($value))) {
                    $errors[$name] = $formattedName . ' can not be more than ' . $field['maxlength'] . ' characters.';
                }
                if (isset($field['minlength']) && $field['minlength'] > trim(strlen($value))) {
                    $errors[$name] = $formattedName . ' must be more than ' . $field['minlength'] . ' characters.';
                }
                if (isset($field['type']) && $field['type'] == 'number') {
                    $value = (float) $value;

                    if (isset($field['max']) && $field['max'] < $value) {
                        $errors[$name] = $formattedName . ' must be a number less than ' . $field['max'] . '.';
                    }
                    if (isset($field['min']) && $field['min'] > $value) {
                        $errors[$name] = $formattedName . ' must be a number greater than ' . $field['min'] . '.';
                    }
                }

                if (isset($field['pattern']) && !preg_match('/' . str_replace('/', '\\/', $field['pattern']) . '/', $value)) {
                    $errors[$name] = $formattedName . ' is not in a valid format.';
                }
            }

            $formData[$name] = $value;
        }

        // validate recaptcha
        $config = Config::getInstance();
        if ($config->recaptcha_on && !$this->isValidCaptcha()) {
            $errors['recaptcha'] = 'An incorrect CAPTCHA code was entered.';
        }

        $this->errors = $errors;
        $this->data = $formData;
    }

    /**
     * Is email address valid
     *
     * @param string $email
     * @return bool
     */
    protected function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate ReCAPTCHA input
     *
     * @return bool
     */
    protected function isValidCaptcha() {
        $config = Config::getInstance();
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret'   => $config->recaptcha_secret_key,
            'response' => $_POST['g-recaptcha-response'],
            'remoteip' => $_SERVER['REMOTE_ADDR']
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );

        try {
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            return (bool) json_decode($result)->success;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Has form submission passed all validation?
     *
     * @return bool
     */
    public function isValid() {
        return !count($this->errors);
    }

    /**
     * Get an array of form validation errors
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get an array of HTML formatted form validation errors
     *
     * @return mixed
     */
    public function getFormattedErrors() {
        $alertTemplate = file_get_contents(APPLICATION_PATH . '/layouts/_system/alert.html');
        $rowTemplate = file_get_contents(APPLICATION_PATH . '/layouts/_system/alert-row.html');
        $errorMessages = array();
        foreach ($this->errors as $error) {
            $errorMessages[] = str_replace('[[__message]]', $error, $rowTemplate);
        }
        return str_replace('[[__rows]]', implode("\n", $errorMessages), $alertTemplate);
    }
}