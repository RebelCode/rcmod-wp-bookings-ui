# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.hostname = "rcmod-wp-bookings-ui"

  config.vm.network "private_network", ip: "192.168.100.11"
  config.vm.network "forwarded_port", guest: 22, host: 1111, host_ip: "127.0.0.1", id: 'ssh'

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--memory", "1536", "--cpus", "2"]
  end

  config.vm.synced_folder ".", "/var/www/project", owner: "vagrant", group: "www-data", mount_options: ["dmode=777,fmode=777"]

  $script = <<-SCRIPT

    # Preparing machine
    sudo apt-get update
    sudo apt-get upgrade

    # Installing Node.js
    curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
    sudo apt-get install -y nodejs
    mkdir /home/vagrant/.npm-global
    npm config set prefix "/home/vagrant/.npm-global"
    echo "export PATH=/home/vagrant/.npm-global/bin:$PATH" >> /home/vagrant/.profile
    source /home/vagrant/.profile
    cd /var/www/project

    # Requirements for phpbrew
    sudo apt-get build-dep php5
    sudo apt-get install -y \
        php5 \
        php5-dev \
        php-pear \
        autoconf \
        automake \
        curl \
        libcurl4-gnutls-dev \
        build-essential \
        libxslt1-dev \
        re2c \
        libxml2 \
        libxml2-dev \
        php5-cli \
        bison \
        libbz2-dev \
        libreadline-dev \
        libfreetype6 \
        libfreetype6-dev \
        libpng12-0 \
        libpng12-dev \
        libjpeg-dev \
        libjpeg8-dev \
        libjpeg8 \
        libgd-dev \
        libgd3 \
        libxpm4 \
        libltdl7 \
        libltdl-dev \
        libssl-dev \
        openssl \
        gettext \
        libgettextpo-dev \
        libgettextpo0 \
        php5-cli \
        libmcrypt-dev \
        libicu-dev

    # Installing phpbrew
    curl -L -O https://github.com/phpbrew/phpbrew/raw/master/phpbrew
    chmod +x phpbrew
    sudo mv phpbrew /usr/bin/phpbrew
    phpbrew init
    phpbrew known --update
    phpbrew update

    # Install PHP 5.4
    phpbrew install 5.4.22 +default
    echo "source ~/.phpbrew/bashrc" >> ~/.bashrc
    phpbrew switch 5.4.22

    # Install Git
    sudo apt-get install -y git

    # Always connect to GitHub using HTTPS for read-only access:
    # otherwise would have to install a public key
    git config --global url."https://github.com/".insteadOf git@github.com:
    git config --global url."https://".insteadOf ssh://

    wget https://github.com/phingofficial/phing/releases/download/2.16.1/phing-2.16.1.phar
    sudo chmod +x phing-2.16.1.phar
    sudo mv phing-2.16.1.phar /usr/bin/phing

    # Allow GitHub
    ssh-keyscan github.com >> githubKey
    cat githubKey >> ~/.ssh/known_hosts
EOL

  SCRIPT

  config.vm.provision "shell", inline: $script, privileged: false
end