function RegisterMenuItems(){
    return [
        {
            header: '',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: '', icon: 'home',text: 'Home'},
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
        	    {action: 'users', icon: 'people',text: 'Users'},
        	    {action: 'groups', icon: 'group_add',text: 'Groups'}
	        ],
	        allowedRoles: ['admin']
        },
        {
            header: 'Customer Management',
            showHeader: true,
            showSeparator: true,
            items: [
        	    {action: 'customers', icon: 'group_add',text: 'Customers'}
	        ],
	        allowedRoles: ['superadmin']
        }
    ]
}

