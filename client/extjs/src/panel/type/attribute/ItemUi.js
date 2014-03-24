/*!
 * Copyright (c) Metaways Infosystems GmbH, 2014
 * LGPLv3, http://www.arcavias.com/license
 */


Ext.ns('MShop.panel.attribute.type');

MShop.panel.attribute.type.ItemUi = Ext.extend(MShop.panel.AbstractItemUi, {

	siteidProperty : 'attribute.type.siteid',


	initComponent : function() {

		MShop.panel.AbstractItemUi.prototype.setSiteCheck( this );

		this.items = [ {
			xtype : 'tabpanel',
			activeTab : 0,
			border : false,
			itemId : 'MShop.panel.attribute.type.ItemUi',
			plugins : [ 'ux.itemregistry' ],
			items : [ {
				xtype : 'panel',
				title : MShop.I18n.dt( 'client/extjs', 'Basic' ),
				border : false,
				layout : 'hbox',
				layoutConfig : {
					align : 'stretch'
				},
				itemId : 'MShop.panel.attribute.type.ItemUi.BasicPanel',
				plugins : [ 'ux.itemregistry' ],
				defaults : {
					bodyCssClass : this.readOnlyClass
				},
				items : [ {
					xtype : 'form',
					flex : 1,
					ref : '../../mainForm',
					autoScroll : true,
					items : [ {
						xtype : 'fieldset',
						style: 'padding-right: 25px;',
						border : false,
						labelAlign : 'top',
						defaults: {
							anchor : '100%',
							readOnly : this.fieldsReadOnly
						},
						items : [ {
							xtype : 'displayfield',
							fieldLabel : MShop.I18n.dt( 'client/extjs',  'ID' ),
							name : 'attribute.type.id'
						}, {
							xtype : 'MShop.elements.status.combo',
							name : 'attribute.type.status',
							allowBlank : false
						}, {
							xtype : 'MShop.elements.domain.combo',
							name : 'attribute.type.domain',
							allowBlank : false
						}, {
							xtype : 'textfield',
							name : 'attribute.type.code',
							fieldLabel : MShop.I18n.dt( 'client/extjs', 'Code' ),
							allowBlank : false,
							emptyText : MShop.I18n.dt( 'client/extjs', 'Unique code (required)' )
						}, {
							xtype : 'textfield',
							name : 'attribute.type.label',
							fieldLabel : MShop.I18n.dt( 'client/extjs', 'Label' ),
							allowBlank : false,
							maxLength : 255,
							emptyText : MShop.I18n.dt( 'client/extjs', 'Internal name (required)' )
						}, {
							xtype : 'displayfield',
							fieldLabel : MShop.I18n.dt( 'client/extjs', 'Created' ),
							name : 'attribute.type.ctime'
						}, {
							xtype : 'displayfield',
							fieldLabel : MShop.I18n.dt( 'client/extjs', 'Last modified' ),
							name : 'attribute.type.mtime'
						}, {
							xtype : 'displayfield',
							fieldLabel : MShop.I18n.dt( 'client/extjs', 'Editor' ),
							name : 'attribute.type.editor'
						}]
					} ]
				} ]
			} ]
		} ];

		MShop.panel.attribute.type.ItemUi.superclass.initComponent.call(this);
	},

	afterRender : function()
	{
		var label = this.record ? this.record.data['attribute.type.label'] : MShop.I18n.dt( 'client/extjs', 'new' );
		//#: Attribute type item panel title with type label ({0}) and site code ({1)}
		var string = MShop.I18n.dt( 'client/extjs', 'Attribute type: {0} ({1})' );
		this.setTitle( String.format( string, label, MShop.config.site["locale.site.label"] ) );

		MShop.panel.attribute.type.ItemUi.superclass.afterRender.apply( this, arguments );
	}
});

Ext.reg('MShop.panel.attribute.type.itemui', MShop.panel.attribute.type.ItemUi);
