var modImporter = function (config) {
    config = config || {};
	modImporter.superclass.constructor.call(this, config);
};
Ext.extend(modImporter, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('mdi', modImporter);

modImporter = new modImporter();