function RegisterMenuItems(){
    return [
        {
            header: '',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: '', icon: 'home', color: 'blue', text: 'Home'}
	        ],
	        allowedRoles: ['user', 'admin', 'superadmin']
        },
        {
            header: '',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: 'tasks', icon: 'assignment_turned_in', color: 'green', text: 'Tasks'},
        	    {action: 'search', icon: 'search', color: 'brown', text: 'Search'},
        	    {action: 'reports', icon: 'pie_chart', color: 'purple', text: 'Reports'},
        	    {action: 'alerts', icon: 'alarm', color: 'red', text: 'Alerts'}
	        ],
	        allowedRoles: ['user', 'admin']
        },
        {
            header: 'Administration',
            showHeader: true,
            showSeparator: true,
            items: [
        	    {action: 'settings', icon: 'settings', color: '', text: 'Settings'},
        	    {action: 'categories', icon: 'list', color: 'orange', text: 'Categories'},
        	    {action: 'users', icon: 'person', color: 'blue', text: 'Users'},
        	    {action: 'groups', icon: 'group', color: 'green', text: 'Groups'}
	        ],
	        allowedRoles: ['admin']
        },
        {
            header: 'Customer Management',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: 'organizations', icon: 'people_outline', color: '', text: 'Organizations'}
	        ],
	        allowedRoles: ['superadmin']
        }
    ];
}