function RegisterRoutes() {
    return [
        {route: '', template: 'home/template', controller: 'home'},
        {route: 'sign-in', template: 'auth/sign-in', controller: 'auth'},
        // {route: 'forgot-password', template: 'auth/forgot-password', controller: 'auth'},
        {route: 'organizations', template: 'organizations/list', controller: 'organizations'},
        {route: 'organizations/new', template: 'organizations/new', controller: 'organizations'},
        {route: 'organizations/:id', template: 'organizations/edit', controller: 'organizations'}
    ];
}

