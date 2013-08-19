Vagrant.configure("2") do |config|
    config.vm.hostname = "lbwreg"
    config.omnibus.chef_version = :latest
    config.vm.box = "Berkshelf-CentOS-6.3-x86_64-minimal"
    config.vm.box_url = "https://dl.dropbox.com/u/31081437/Berkshelf-CentOS-6.3-x86_64-minimal.box"
    config.vm.network :private_network, ip: "33.33.33.10"
    config.vm.network :forwarded_port, guest: 80, host: 8080
    config.vm.synced_folder ".", "/var/www"
    config.ssh.max_tries = 40
    config.ssh.timeout   = 120
    config.berkshelf.enabled = true
    config.vm.provision :chef_solo do |chef|
        chef.json = {
            mysql:  { server_root_password: 'rootpass',
                        server_debian_password: 'rootpass',
                        server_repl_password: 'rootpass'},
            apache: { default_site_enabled: true },
            composer: { group: "apache" }
        }
        chef.run_list = [
            "recipe[mysql::server]",
            "recipe[php::module_mysql]",
            "recipe[apache2]",
            "recipe[apache2::mod_php5]",
            "recipe[apache2::mod_rewrite]",
            "recipe[composer]"
        ]
    end
end
