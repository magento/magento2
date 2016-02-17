[Reflection.Assembly]::LoadWithPartialName("zend.service.windowsazure.ServiceRuntime")

$rdRoleId = [Environment]::GetEnvironmentVariable("RdRoleId", "Machine")

[Environment]::SetEnvironmentVariable("RdRoleId", [zend.service.windowsazure.ServiceRuntime.RoleEnvironment]::CurrentRoleInstance.Id, "Machine")
[Environment]::SetEnvironmentVariable("RoleName", [zend.service.windowsazure.ServiceRuntime.RoleEnvironment]::CurrentRoleInstance.Role.Name, "Machine")
[Environment]::SetEnvironmentVariable("RoleInstanceID", [zend.service.windowsazure.ServiceRuntime.RoleEnvironment]::CurrentRoleInstance.Id, "Machine")
[Environment]::SetEnvironmentVariable("RoleDeploymentID", [zend.service.windowsazure.ServiceRuntime.RoleEnvironment]::DeploymentId, "Machine")


if ($rdRoleId -ne [zend.service.windowsazure.ServiceRuntime.RoleEnvironment]::CurrentRoleInstance.Id) {
    Restart-Computer
}

[Environment]::SetEnvironmentVariable('Path', $env:RoleRoot + '\base\x86;' + [Environment]::GetEnvironmentVariable('Path', 'Machine'), 'Machine')