modImporter.combo.Type = function(config) {
  config = config || {};
  Ext.applyIf(config, {
    fieldLabel: _('mdi_type'),
    allowBlank: false,
    anchor: '99%',
    name: 'type',
    width: 350,
    hiddenName: 'type',
    displayField: 'name',
    valueField: 'type',
    editable: false,
    fields: ['name', 'type'],
    pageSize: 0,
    emptyText: 'Выберите тип импорта',
    hideMode: 'offsets',
    url: modImporter.config.connector_url + 'connector.php',
    baseParams: {
      action: 'mgr/import/gettypes'
    }
  });
  modImporter.combo.Type.superclass.constructor.call(this, config);
};
Ext.extend(modImporter.combo.Type, MODx.combo.ComboBox);
Ext.reg('mdi-combo-type', modImporter.combo.Type);

modImporter.combo.Format = function(config) {
  config = config || {};
  Ext.applyIf(config, {
    fieldLabel: _('mdi_format'),
    allowBlank: false,
    anchor: '99%',
    name: 'format',
    width: 350,
    hiddenName: 'format',
    displayField: 'name',
    valueField: 'format',
    fields: ['name', 'format'],
    emptyText: 'Выберите формат импорта',
    store: new Ext.data.ArrayStore({
      id: 0,
      fields: ['name', 'format'],
      data: [
        ['Файл', 'file']
        , ['Ссылка', 'url']
      ]
    }),
    mode: 'local',
    listeners: {
      render: function(field) {
        field.setValue('file');
      }
    }
  });
  modImporter.combo.Format.superclass.constructor.call(this, config);
};
Ext.extend(modImporter.combo.Format, MODx.combo.ComboBox);
Ext.reg('mdi-combo-format', modImporter.combo.Format);

modImporter.combo.FileBrowser = function(config) {
  config = config || {};

  Ext.applyIf(config, {
    fieldLabel: _('mdi_select_file'),
    anchor: '99%',
    width: 350,
    name: 'file',
    source: modImporter.config.source
  });
  modImporter.combo.FileBrowser.superclass.constructor.call(this, config);
};
Ext.extend(modImporter.combo.FileBrowser, MODx.combo.Browser);
Ext.reg('mdi-combo-filebrowser', modImporter.combo.FileBrowser);
