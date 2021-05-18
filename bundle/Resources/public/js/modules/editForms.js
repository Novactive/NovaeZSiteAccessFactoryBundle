export const NovaeZSiteAccessFactoryEditFormModule = function () {
    function _init ($, $app) {

        var $form = $('form[novaezsiteaccessfactory]', $app);
        var $saveButton = $("#novaezsiteaccessfactory_admin_save-tab", $app);
        var $cancelButton = $("#novaezsiteaccessfactory_admin_cancel-tab", $app);

        $cancelButton.click(function () {
            history.back();
        });
        $saveButton.click(function () {
            $form.submit();
        });

    }

    return { init: _init };
}();

