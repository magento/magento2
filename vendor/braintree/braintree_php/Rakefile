task :default => :test
task :test => %w[test:unit test:integration]

namespace :test do
  desc "run unit tests"
  task :unit do
    run_php_test_suite("unit")
  end

  desc "run integration tests"
  task :integration do
    run_php_test_suite("integration")
  end

  desc "run a single test file"
  task :single_test, :file_path do |t, args|
    run_php_test_file(args[:file_path])
  end
end

def run_php_test_suite(test_suite)
  sh "./vendor/bin/phpunit --testsuite #{test_suite}"
end

def run_php_test_file(test_file)
  sh "./vendor/bin/phpunit #{test_file}"
end
