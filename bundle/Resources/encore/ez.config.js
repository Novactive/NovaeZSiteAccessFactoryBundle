const path = require('path');

module.exports = (Encore) => {
    Encore.addEntry('nova_ezsiteaccessfactory', [
        path.resolve(__dirname, '../public/js/modules/editForms.js'),
        path.resolve(__dirname, '../public/js/novaezsiteaccessfactory.js'),
        path.resolve(__dirname, '../public/css/novaezsiteaccessfactory.scss')
    ]);
};
