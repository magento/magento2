case $TEST_SUITE in
    static)
        TEST_FILTER='--filter "Magento\\Test\\Php\\LiveCodeTest"' || true
        phpunit -c dev/tests/$TEST_SUITE $TEST_FILTER
        grunt static
        ;;
    js)
        grunt spec
        ;;
esac
