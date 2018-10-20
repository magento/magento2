# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

    # BOX SETTINGS
    config.vm.box = "sternpunkt/jimmybox"

    # NETWORKING
    ############################################################################

    config.vm.hostname = "magento2dev"

    # Private Network
    config.vm.network "private_network", ip: "192.168.8.10"

    # port forwarding must be enabled for vagrant share
    config.vm.network "forwarded_port", guest: 80, host: 8080, auto_correct: true

    # Public network:
    # uncomment the lines and add your own config (bridge, ip, etc.)

    # config.vm.network "public_network",
    # :bridge => "en0: WLAN (Airport)",
    # ip: "192.168.10.201", :netmask => "255.255.255.0", auto_config: true

    # SYNCED FOLDERS
    ############################################################################

    # DEFAULT:
    config.vm.synced_folder "./magento2/public", "/var/www", :mount_options => ["dmode=777", "fmode=777"]

    # NFS:
    # you should try NFS share - it performs much better than the default synced folder!
    # config.vm.synced_folder "./public", "/var/www", :nfs => { :mount_options => ["dmode=777","fmode=777"] }

    # RSYNC:
    # if you are using a framework that contains many files rsync can provide best performance
    # You can use vagrant rsync-auto to sync changes automatically to your vagrant box.
    # config.vm.synced_folder "./public", "/var/www", type: "rsync", rsync__auto: true

end
