module.exports = {

    el: '#settings',

    data: function () {
        return window.$data;
    },

    methods: {

        save: function () {
            this.$http.post('admin/system/settings/config', {name: 'sitemap', config: this.config}).then(function () {
                        this.$notify('Settings saved.');
                    }, function (data) {
                        this.$notify(data, 'danger');
                    }
                );
        },

        generate: function () {
            this.$notify('Please wait until "Sitemap generated" message will show. It can take some time.', {status:'warning', timeout: 0});
            this.$http.post('/admin/sitemap/generate',{config: this.config}).then(function (data) {
                        this.$notify('Sitemap generated.');
                    }, function (data) {
                        this.$notify(data, 'danger');
                    }
                );
        }

    }

};

Vue.ready(module.exports);