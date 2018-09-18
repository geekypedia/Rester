function RegisterRoutes() {
    return {
        customRoutes: [
            {route: '', template: 'home/template', controller: 'home'},
            {route: 'sign-in', template: 'auth/sign-in', controller: 'auth'},
            // {route: 'forgot-password', template: 'auth/forgot-password', controller: 'auth'},
            {route: 'unauthorized', template: 'auth/unauthorized', controller: 'unauthorized'}
        ],
        easyRoutes: ['organizations', 'users']
    };
}

