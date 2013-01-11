Exec {
  path => ["/usr/bin", "/bin", "/usr/sbin", "/sbin", "/usr/local/bin", "/usr/local/sbin"]
}

group {
  'puppet': ensure => present
}

exec {
  'apt-get-update': command => '/usr/bin/apt-get update'
}

package {
  ['python-software-properties', 'curl']: ensure => present, require => Exec['apt-get-update']
}

exec {
  'apt-add-repository-ondrej-php5':
    command => '/usr/bin/add-apt-repository -y ppa:ondrej/php5; /usr/bin/apt-get update', #ugly but works
    require => Package['python-software-properties'],
    unless  => 'test -f /etc/apt/sources.list.d/ondrej-php5-*.list'
}

if $virtual == 'virtualbox' and $fqdn == '' {
    $fqdn = 'localhost'
}

package {
  ['apache2', 'libapache2-mod-php5']: ensure => present, require => Exec['apt-get-update']
}

service {
  'apache2': ensure => running, enable => true,
    require => Package['apache2'],
    subscribe => [File["/etc/apache2/mods-enabled/rewrite.load"], File["/etc/apache2/sites-available/default"]]
}

file { 
  '/etc/apache2/mods-enabled/rewrite.load': ensure  => link, require => Package['apache2'],
    target  => '/etc/apache2/mods-available/rewrite.load'
}

file { 
  '/etc/apache2/sites-available/default': ensure => present, require => Package['apache2'],
    source => '/vagrant/puppet/templates/vhost'
}

exec { 
  'echo "ServerName localhost" | sudo tee /etc/apache2/conf.d/fqdn': require => Package['apache2']
}

package {
  ['php5', 'php5-cli', 'php5-sqlite', 'php5-gd', 'php5-curl', 'php-apc', 'php5-mcrypt', 'php5-xdebug']:
    ensure => present, require => Exec['apt-add-repository-ondrej-php5']
}

exec { 
  "sed -i 's|#|//|' /etc/php5/cli/conf.d/mcrypt.ini": require => Package['php5'],
    onlyif  => "test -f /etc/php5/cli/conf.d/mcrypt.ini"
}
