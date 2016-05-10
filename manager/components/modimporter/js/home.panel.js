modImporter.panel.Import = function(config) {

  config = config || {};
  this.config = config;

  Ext.applyIf(config, {
    url: modImporter.config.connector_url + 'connector.php',
    bodyStyle: 'padding: 10px 15px;', // ,width: 400
    layout: 'form',
    items: [{
      html: '<h2>modImporter</h2>',
      border: false,
      bodyStyle: 'margin: 10px 0 10px 0',
      cls: 'modx-page-header'
    }, {
      xtype: 'modx-tabs',
      id: 'referral-settings-tabs',
      bodyStyle: 'padding: 10px',
      defaults: {
        border: false,
        autoHeight: true
      },
      border: true,
      hideMode: 'offsets',
      stateful: true,
      stateId: 'modimporter-settings-tabpanel',
      stateEvents: ['tabchange'],
      getState: function() {
        return {
          activeTab: this.items.indexOf(this.getActiveTab())
        };
      },
      items: [{
        title: _('modimporter'),
        items: [
          {
            xtype: 'mdi-grid-import'
          }
        ]
      }, {
        title: 'Экспорт',
        items: [
          {
            xtype: 'mdi-grid-export'
          }
        ]
      }
      ]
    }
    ]
  });

  modImporter.panel.Import.superclass.constructor.call(this, config);
};

Ext.extend(modImporter.panel.Import, MODx.Panel, {



});

Ext.reg('modimporter-panel-import', modImporter.panel.Import);
