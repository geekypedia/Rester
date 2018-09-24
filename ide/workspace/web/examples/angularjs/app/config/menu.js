function RegisterMenuItems(){
    return [
        {
            header: '',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: '', icon: 'home',text: 'Home'}
	        ],
	        allowedRoles: ['user', 'admin', 'superadmin']
        },
        {
            header: '',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: 'tasks', icon: 'assignment_turned_in',text: 'Tasks'},
        	    {action: 'search', icon: 'search',text: 'Search'},
        	    {action: 'reports', icon: 'pie_chart',text: 'Reports'},
        	    {action: 'alerts', icon: 'alarm',text: 'Alerts'}
	        ],
	        allowedRoles: ['user', 'admin']
        },
        {
            header: 'Administration',
            showHeader: true,
            showSeparator: true,
            items: [
        	    {action: 'settings', icon: 'settings',text: 'Settings'},
        	    {action: 'categories', icon: 'list',text: 'Categories'},
        	    {action: 'users', icon: 'person',text: 'Users'},
        	    {action: 'groups', icon: 'group',text: 'Groups'}
	        ],
	        allowedRoles: ['admin']
        },
        {
            header: 'Customer Management',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: 'organizations', icon: 'people_outline',text: 'Organizations'}
	        ],
	        allowedRoles: ['superadmin']
        }
    ];
}