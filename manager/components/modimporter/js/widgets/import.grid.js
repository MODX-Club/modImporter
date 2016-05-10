modImporter.grid.ImportGrid = function(config) {
  config = config || {};

  this.config = config;

  Ext.applyIf(config, {
    url: modImporter.config.connector_url + 'connector.php',
    baseParams: {
      action: 'mgr/import/getlist'
    },
    fields: ['id', 'name', 'description', 'format', 'source', 'type', 'lastimportdon', 'actions'],
    autoHeight: true,
    paging: true,
    remoteSort: true,
    save_action: 'mgr/import/updatefromgrid',
    autosave: true,
    columns: [
      {
        header: _('mdi_id'),
        dataIndex: 'id',
        width: 50
      }
      , {
        header: _('mdi_name'),
        dataIndex: 'name',
        width: 150,
        editor: {
          xtype: 'textfield',
          allowBlank: false
        }
      }
      , {
        header: _('mdi_format'),
        dataIndex: 'format',
        width: 70,
        editor: {
          xtype: 'textfield',
          allowBlank: false
        }
      }
      , {
        header: _('mdi_type'),
        dataIndex: 'type',
        width: 100,
        editor: {
          xtype: 'textfield',
          allowBlank: false
        }
      }
      , {
        header: _('mdi_source'),
        dataIndex: 'source',
        width: 100,
        editor: {
          xtype: 'textfield',
          allowBlank: false
        }
      }
      , {
        header: _('mdi_lastimportdon'),
        dataIndex: 'lastimportdon',
        width: 100
      }
      , {
        header: _('mdi_actions'),
        dataIndex: 'actions',
        renderer: modImporter.utils.renderActions
      }
    ],
    tbar: [{
      text: _('mdi_menu_create'),
      handler: this.createImport,
      scope: this
    }],
    enableDragDrop: false
  });
  modImporter.grid.ImportGrid.superclass.constructor.call(this, config);
};
Ext.extend(modImporter.grid.ImportGrid, MODx.grid.Grid, {
  windows: {},
  getMenu: function() {
    var m = [];
    m.push({
      text: _('mdi_menu_update'),
      handler: this.updateImport
    });
    m.push('-');
    m.push({
      text: _('mdi_menu_remove'),
      handler: this.removeImport
    });
    this.addContextMenuItem(m);
  },
  renderBoolean: function(value) {
    if (value == 1) {
      return _('yes');
    } else {
      return _('no');
    }
  },
  createImport: function(btn, e) {
    if (!this.windows.createImport) {
      this.windows.createImport = MODx.load({
        xtype: 'mdi-window-import-create',
        fields: this.getImportFields('create'),
        listeners: {
          success: {
            fn: function() {
              this.refresh();
            },
            scope: this
          }
        }
      });
    }
    this.windows.createImport.fp.getForm().reset();
    this.windows.createImport.show(e.target);
  },
  updateImport: function(btn, e) {
    if (!this.menu.record || !this.menu.record.id)
      return false;
    var r = this.menu.record;

    if (!this.windows.updateImport) {
      this.windows.updateImport = MODx.load({
        xtype: 'mdi-window-import-update',
        record: r,
        fields: this.getImportFields('update'),
        listeners: {
          success: {
            fn: function() {
              this.refresh();
            },
            scope: this
          }
        }
      });
    }
    this.windows.updateImport.fp.getForm().reset();
    this.windows.updateImport.show(e.target);
    this.windows.updateImport.fp.getForm().setValues(r);
  },
  removeImport: function(btn, e) {
    if (!this.menu.record)
      return false;

    MODx.msg.confirm({
      title: _('mdi_menu_remove') + '"' + this.menu.record.name + '"',
      text: _('mdi_menu_import_remove_confirm'),
      url: this.config.url,
      params: {
        action: 'mgr/import/remove',
        id: this.menu.record.id
      },
      listeners: {
        success: {
          fn: function(r) {
            this.refresh();
          },
          scope: this
        }
      }
    });
  },
  getImportFields: function(type) {
    return [
      {
        xtype: 'hidden',
        name: 'id'
      }
      , {
        xtype: 'textfield',
        fieldLabel: _('mdi_name'),
        name: 'name',
        allowBlank: false,
        anchor: '99%'
      }
      , {
        xtype: 'textfield',
        fieldLabel: _('mdi_description'),
        name: 'description',
        allowBlank: true,
        anchor: '99%'
      }
      , {
        xtype: 'mdi-combo-format',
        name: 'format'
      }
      , {
        xtype: 'mdi-combo-type',
        name: 'type'
      }
      , {
        xtype: 'mdi-combo-filebrowser',
        fieldLabel: _('mdi_source'),
        name: 'source',
        allowBlank: false,
        anchor: '99%'
      }
    ];
  },
  startImport: function(btn, e) {
    if (!this.menu.record)
      return false;
    new modImporter.window.Console({
      'register': 'mgr',
      url: this.url,
      baseParams: {
        type: this.menu.record.type || 'console',
        format: this.menu.record.format,
        filename: this.menu.record.source,
        source: modImporter.config.source,
        importId: this.menu.record.id
      },
      listeners: {
        close: function() {
          this.refresh();
        },
        scope: this
      }
    }).show();
  },
  onClick: function(e) {
    var elem = e.getTarget();
    if (elem.nodeName == 'BUTTON') {
      var row = this.getSelectionModel().getSelected();
      if (typeof (row) != 'undefined') {
        var action = elem.getAttribute('action');
        this.menu.record = row.data;
        return this[action](this, e);
      }
    }
    return this.processEvent('click', e);
  }


});
Ext.reg('mdi-grid-import', modImporter.grid.ImportGrid);


modImporter.window.CreateImport = function(config) {
  config = config || {};
  this.ident = config.ident || 'mecitem' + Ext.id();
  Ext.applyIf(config, {
    title: _('mdi_menu_create'),
    id: this.ident,
    width: 600,
    autoHeight: true,
    labelAlign: 'left',
    labelWidth: 180,
    url: modImporter.config.connector_url + 'connector.php',
    action: 'mgr/import/create',
    fields: config.fields,
    keys: [{
      key: Ext.EventObject.ENTER,
      shift: true,
      fn: function() {
        this.submit()
      },
      scope: this
    }]
  });
  modImporter.window.CreateImport.superclass.constructor.call(this, config);
};
Ext.extend(modImporter.window.CreateImport, MODx.Window);
Ext.reg('mdi-window-import-create', modImporter.window.CreateImport);


modImporter.window.UpdateImport = function(config) {
  config = config || {};
  this.ident = config.ident || 'meuitem' + Ext.id();
  Ext.applyIf(config, {
    title: _('mdi_menu_update'),
    id: this.ident,
    width: 600,
    autoHeight: true,
    labelAlign: 'left',
    labelWidth: 180,
    url: modImporter.config.connector_url + 'connector.php',
    action: 'mgr/import/update',
    fields: config.fields,
    keys: [{
      key: Ext.EventObject.ENTER,
      shift: true,
      fn: function() {
        this.submit()
      },
      scope: this
    }]
  });
  modImporter.window.UpdateImport.superclass.constructor.call(this, config);
};
Ext.extend(modImporter.window.UpdateImport, MODx.Window);
Ext.reg('mdi-window-import-update', modImporter.window.UpdateImport);
