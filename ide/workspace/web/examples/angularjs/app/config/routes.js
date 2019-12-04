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
        ], //customRoutes finds template inside app/modules
        easyRoutes: ['organizations', 'users', 'groups', 'categories', 'tasks'], // Provide the names of your tables here and make a copy of app/modules/tasks with the same name
        autoRoutes: [], // Either you can provide the names of your tables here, or you can excluded some of the routes below in autoRoutesExcludes
        autoRoutesExcluded: [] // If you don't specify autoRoutes, all of your tables except routes defined in customRoutes and easyRoutes will automatically have UI under Masters menu. You can exclude some of those tables here
    };
}
