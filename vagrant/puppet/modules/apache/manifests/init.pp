class apache 
{      
    $packages = [
        "apache2",
        "libapache2-mod-php5"
    ]
    package 
    { 
        $packages:
            ensure  => present,
            require => Exec['apt-get update']
    }
    
    service 
    { 
        "apache2":
            ensure      => running,
            enable      => true,
            require     => Package['apache2'],
            subscribe   => [
                File["/etc/apache2/mods-enabled/rewrite.load"],
                File["/etc/apache2/sites-available/default"]
            ],
    }

    file 
    { 
        "/etc/apache2/mods-enabled/rewrite.load":
            ensure  => link,
            target  => "/etc/apache2/mods-available/rewrite.load",
            require => Package['apache2'],
    }

    file 
    { 
        "/etc/apache2/sites-available/default":
            ensure  => present,
            source  => "/vagrant/puppet/templates/vhost",
            require => Package['apache2'],
    }

    exec 
    { 
        'echo "ServerName localhost" | sudo tee /etc/apache2/conf.d/fqdn':
            require => Package['apache2'],
    }
}
