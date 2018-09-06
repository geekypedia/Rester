function RegisterRoutes() {
    return [
        {route: '', template: 'home/template', controller: 'home'},
        {route: 'sign-in', template: 'auth/sign-in', controller: 'auth'},
        // {route: 'forgot-password', template: 'auth/forgot-password', controller: 'auth'},
    ];
}

