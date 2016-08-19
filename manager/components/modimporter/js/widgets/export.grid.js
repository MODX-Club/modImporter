modImporter.grid.ExportGrid = function(config) {
  config = config || {};

  this.config = config;

  Ext.applyIf(config, {
    url: modImporter.config.connector_url + 'connector.php',
    baseParams: {
      action: 'mgr/export/getlist'
    },
    fields: ['id', 'url', 'exportdon', 'params', 'actions'],
    autoHeight: true,
    paging: true,
    remoteSort: true,
    save_action: 'mgr/export/updatefromgrid',
    autosave: true,
    columns: [
      {
        header: _('mdi_id'),
        dataIndex: 'id',
        width: 50
      }
      , {
        header: _('mdi_url'),
        dataIndex: 'url',
        width: 150,
        renderer: function(value, cell, record){
            var url = MODx.config.connectors_url + "index.php?action=browser/file/download&download=1&file="+ value +"&HTTP_MODAUTH="+ MODx.siteId+"&source=" + MODx.config['modimporter.media_source'];
            return '<a href="'+ url +'">'+ value +'</a>';
        }
      }
      , {
        header: _('mdi_exportdon'),
        dataIndex: 'exportdon',
        width: 150
      }
    ],
    tbar: [{
      xtype: 'mdi-combo-type',
      name: 'type',
      emptyText: 'Выберите тип экспорта',
      hideMode: 'offsets',
      baseParams: {
        action: 'mgr/export/gettypes'
      },
      listeners: {
        select: function() {
          modImporter.config.exportType = this.getValue();
        }
      }
    }, {
      text: _('mdi_export_start'),
      handler: this.startExport,
      scope: this
    }],
    enableDragDrop: false
  });
  modImporter.grid.ExportGrid.superclass.constructor.call(this, config);
};
Ext.extend(modImporter.grid.ExportGrid, MODx.grid.Grid, {
  windows: {},
  getMenu: function() {
    var m = [];
    m.push({
      text: _('mdi_menu_remove'),
      handler: this.removeExport
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
  startExport: function(btn, e) {
    new modImporter.window.Console({
      'register': 'mgr',
      url: this.url,
      baseParams: {
        type: modImporter.config.exportType || 'export',
        source: modImporter.config.source
      },
      listeners: {
        close: function() {
          this.refresh();
        },
        scope: this
      }
    }).show();
  },
  removeExport: function(btn, e) {
    if (!this.menu.record)
      return false;

    MODx.msg.confirm({
      title: _('mdi_menu_remove'),
      text: _('mdi_menu_export_remove_confirm'),
      url: this.config.url,
      params: {
        action: 'mgr/export/remove',
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
  }
});
Ext.reg('mdi-grid-export', modImporter.grid.ExportGrid);
