# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.hostname = "rcmod-wp-bookings-ui"

  config.vm.network "private_network", ip: "192.168.100.11"
  config.vm.network "forwarded_port", guest: 22, host: 1111, host_ip: "127.0.0.1", id: 'ssh'

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--memory", "1536", "--cpus", "2"]
    vb.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root", "1"]
  end

  # Dependency caching
  if Vagrant.has_plugin?("vagrant-cachier")
    # Configure cached packages to be shared between instances of the same base box.
    # More info on http://fgrehm.viewdocs.io/vagrant-cachier/usage
    config.cache.scope = :box

    # For more information please check http://docs.vagrantup.com/v2/synced-folders/basic_usage.html
  end

  config.vm.synced_folder ".", "/var/www/project", owner: "vagrant", group: "www-data", mount_options: ["dmode=777,fmode=777"]

  $script = <<-SCRIPT

    # Preparing machine
    sudo apt-get update
    sudo apt-get upgrade

    # Installing Node.js
    if ! [[ $(command -v npm) ]]; then
        curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
        sudo apt-get install -y nodejs
        mkdir /home/vagrant/.npm-global
        npm config set prefix "/home/vagrant/.npm-global"
        echo "export PATH=/home/vagrant/.npm-global/bin:$PATH" >> /home/vagrant/.profile
        source /home/vagrant/.profile
        cd /var/www/project
    fi

    # Requirements for phpbrew
    if ! [[ $(command -v phpbrew) ]]; then
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
    fi

    # Install PHP 5.4

    if ! [[ $(phpbrew list | grep 'php-5.4.22') ]]; then
        phpbrew install 5.4.22 +default
        echo "source ~/.phpbrew/bashrc" >> ~/.bashrc
    fi
    phpbrew switch 5.4.22

    # Install Git
    if ! [[ $(command -v git) ]]; then
        sudo apt-get install -y git

        # Always connect to GitHub using HTTPS for read-only access:
        # otherwise would have to install a public key
        git config --global url."https://github.com/".insteadOf git@github.com:
        git config --global url."https://".insteadOf ssh://

        # Allow GitHub
        ssh-keyscan github.com >> githubKey
        cat githubKey >> ~/.ssh/known_hosts
    fi

    if ! [[ $(command -v phing) ]]; then
        wget https://github.com/phingofficial/phing/releases/download/2.16.1/phing-2.16.1.phar
        sudo chmod +x phing-2.16.1.phar
        sudo mv phing-2.16.1.phar /usr/bin/phing
    fi

    if ! [[ $(command -v composer) ]]; then
        curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
    fi

  SCRIPT

  config.vm.provision "shell", inline: $script, privileged: false
end
