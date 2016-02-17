# Development using a virtual machine

You can set up a development virtual machine for ZF1 unit testing and library 
development following these simple instructions.

### 1. Install requirements for VM. (Note: these are not required by ZF1 itself)

- VirtualBox (https://www.virtualbox.org/)
- Ruby (http://www.ruby-lang.org/)
- Vagrant (http://vagrantup.com/)

### 2. Checkout repository to any location

    git clone git://github.com/zendframework/zf1.git zf1-dev
    cd zf1-dev

### 3. Start the process by running Vagrant.

    vagrant up

This will take a long while as it has to download a VM image and then 
provision it. Once it has finished, it will exit and leave you back at the
command prompt.

### 4. SSH into the VM

    vagrant ssh

### 5. Build a version of PHP.

    php-build.sh 5.3.11

This also takes a while as it compiles PHP for you!
   
### 6. Select PHP to use:

    pe 5.3.11

### 7. Run tests

    cd /vagrant/tests
    phpunit --stderr -d memory_limit=-1 Zend/Acl/AclTest.php
    phpunit --stderr -d memory_limit=-1 Zend/Amf/AllTests.php
    (etc...)

Note that you can repeat items 5 and 6 to create any version if PHP.

## Notes:

- The VM will be running in the background as VBoxHeadless
- HTTP and SSH ports on the VM are forwarded to localhost (22 -> 2222, 80 -> 8081)
- The zf1-dev directory you checked out will be mounted inside the VM at /vagrant
- You can develop by editing the files you cloned in the IDE of you choice.

To stop the VM do one of the following:

    vagrant suspend   # if you plan on running it later
    vagrant halt      # if you wish to turn off the VM, but keep it around
    vagrant destroy   # if you wish to delete the VM completely
    
Also, when any of of the Puppet manifests change (.pp files), it is a good idea to rerun them:

    vagrant provision