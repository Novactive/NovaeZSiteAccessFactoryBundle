jQuery(function () {
    "use strict";
    var $ = jQuery;
    var $app = $(".novaezsiteaccessfactory-app:first, .novaezsiteaccessfactory-quick-nav:first");
    $app.find('[data-toggle="popover"]').popover().click(function () {
        return false;
    });
    NovaeZSiteAccessFactoryEditFormModule.init(jQuery, $app);
    NovaeZSiteaccessFactoryNormalizeModule.init(jQuery, $app);
});
