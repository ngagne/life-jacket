function populateForm(data) {
    if (document.forms.length) {
        for (var i in data) {
            var el = document.forms[0].elements['form[' + i + ']'];
            if (el && data.hasOwnProperty(i)) {
                el.value = data[i];
            }
        }
    }
}
