Ext.onReady(function()
{
	var grid = new IntelliGrid(
	{
		columns: [
			'selection',
			{name: 'type', title: _t('type'), width: 2},
			{name: 'ip', title: _t('ip_address'), width: 110},
			{name: 'item', title: _t('item'), width: 120},
			{name: 'item_id', title: _t('item_id'), width: 50},
			{name: 'status', title: _t('status'), width: 90},
			{name: 'date', title: _t('date'), width: 160, editor: 'date'},
			{name: 'view', title: _t('view'), icon: 'eye', href: intelli.config.admin_url + '/claims/view/{id}/'},
			'delete'
		],
		sorters: [{property: 'date', direction: 'DESC'}]
	}, false);

	grid.toolbar = Ext.create('Ext.Toolbar', {items:[
	{
		emptyText: _t('text'),
		name: 'text',
		listeners: intelli.gridHelper.listener.specialKey,
		width: 275,
		xtype: 'textfield'
	},{
		displayField: 'title',
		editable: false,
		emptyText: _t('status'),
		id: 'fltStatus',
		name: 'status',
		store: grid.stores.statuses,
		typeAhead: true,
		valueField: 'value',
		xtype: 'combo'
	},{
		handler: function(){intelli.gridHelper.search(grid);},
		id: 'fltBtn',
		text: '<i class="i-search"></i> ' + _t('search')
	},{
		handler: function(){intelli.gridHelper.search(grid, true);},
		text: '<i class="i-close"></i> ' + _t('reset')
	}]});

	grid.init();
});