Vagrant.configure("2") do |config|
    config.vm.box = "ubuntu/trusty64"
    config.vm.provision :shell, :path => "bootstrap.sh"
    config.vm.network :forwarded_port, guest: 8000, host: 8000
    config.vm.network "private_network", ip: "192.168.50.4"
    config.vm.provider :virtualbox do |vb|
      vb.name = "dev_host2"
  end
end


