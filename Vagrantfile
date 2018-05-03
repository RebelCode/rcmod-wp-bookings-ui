# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.hostname = "rcmod-wp-bookings-ui"

  config.vm.network "private_network", ip: "192.168.33.10"
  config.vm.network "forwarded_port", guest: 22, host: 1111, host_ip: "127.0.0.1", id: 'ssh'

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--memory", "1536", "--cpus", "2"]
  end

  config.vm.synced_folder ".", "/var/www/project", owner: "vagrant", group: "www-data", mount_options: ["dmode=777,fmode=777"]

  $script = <<-SCRIPT
    curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
    sudo apt-get install -y nodejs
    sudo add-apt-repository -y ppa:ondrej/php
    sudo apt-get -y update
    sudo apt-get install -y php5.6 php5.6-curl php5.6-xml
    wget https://github.com/phingofficial/phing/releases/download/2.16.1/phing-2.16.1.phar
    sudo chmod +x phing-2.16.1.phar
    sudo mv phing-2.16.1.phar /usr/bin/phing
    mkdir /home/vagrant/.npm-global
    npm config set prefix "/home/vagrant/.npm-global"
    echo "export PATH=/home/vagrant/.npm-global/bin:$PATH" >> /home/vagrant/.profile
    source /home/vagrant/.profile
    cd /var/www/project
    php -v
  SCRIPT

  config.vm.provision "shell", inline: $script, privileged: false
end