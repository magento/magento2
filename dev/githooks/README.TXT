Installing Shared Client-Side Hooks
-----------------------------------
Git hooks exist on a per repository basis and cannot be installed during normal git procedures for security reasons.
To install the existing pre-commit hook, simple run the following commands from your magento root directory:
> cp dev/githooks/precommit .git/hooks
> chmod u+x .git/hooks/pre-commit

The pre-commit script will now run before you commit any code, and will reject your commit if the unit tests fail.
If for exceptional reasons, you need to bypass the pre-commit script, please use the following command to commit:
> git commit --no-verify