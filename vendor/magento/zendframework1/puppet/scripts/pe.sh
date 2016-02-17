
function pe () {
        version=$1
        shift

        if [ "$#" == "0" ]; then
                export PATH=/usr/local/php/${version}/bin:/usr/local/bin:/usr/bin:/bin:/vagrant/puppet/scripts
        else
                PATH=/usr/local/php/${version}/bin:$PATH $@
        fi
}

export PATH=/vagrant/puppet/scripts/:$PATH

