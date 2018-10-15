function RegisterRoutes() {
    return {
        customRoutes: [
            {route: '', template: 'home/template', controller: 'home'},
            {route: 'sign-in', template: 'auth/sign-in', controller: 'auth', auth: false},
            {route: 'forgot-password', template: 'auth/forgot-password', controller: 'auth', auth: false},
            {route: 'register', template: 'auth/register', controller: 'auth', auth: false},
            {route: 'profile', template: 'auth/profile', controller: 'profile'},
            {route: 'unauthorized', template: 'auth/unauthorized', controller: 'unauthorized'},
            {route: 'out-of-service', template: 'auth/out-of-service', controller: 'outOfService', auth: false},
            {route: 'settings', template: 'settings/template', controller: 'settings'}
        ],
        easyRoutes: ['organizations', 'users', 'groups', 'tasks']
    };
}