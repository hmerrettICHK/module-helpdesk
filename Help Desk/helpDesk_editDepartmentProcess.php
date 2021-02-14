<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Module\HelpDesk\Domain\DepartmentGateway;
use Gibbon\Module\HelpDesk\Domain\DepartmentPermissionsGateway;
use Gibbon\Domain\User\RoleGateway;

require_once '../../gibbon.php';

require_once './moduleFunctions.php';

$URL = $gibbon->session->get('absoluteURL') . '/index.php?q=/modules/' . $gibbon->session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Help Desk/helpDesk_manageDepartments.php')) {
    $URL .= '/issues_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    $departmentID = $_POST['departmentID'] ?? '';
    
    $departmentGateway = $container->get(DepartmentGateway::class);
    $departmentPermissionsGateway = $container->get(DepartmentPermissionsGateway::class);
    
    if(empty($departmentID) || !$departmentGateway->exists($departmentID)) {
        $URL .= 'helpDesk_manageDepartments.php&return=error1';
        header("Location: {$URL}");
        exit();
    }

    $URL .= "/helpDesk_editDepartment.php&departmentID=$departmentID";

    $departmentName = $_POST['departmentName'] ?? '';
    $departmentDesc = $_POST['departmentDesc'] ?? '';
    $roles = $_POST['roles'] ?? '';

    if (empty($departmentName) || strlen($departmentName) > 55 || empty($departmentDesc) || strlen($departmentDesc) > 128) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    }

    $data = ['departmentName' => $departmentName, 'departmentDesc' => $departmentDesc];

    if (!$departmentGateway->unique($data, ['departmentName'], $departmentID)) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit();
    }

    if (!$departmentGateway->update($departmentID, $data)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }
    //TODO: create an if error for this
    $departmentPermissionsGateway->deleteWhere(['departmentID' => $departmentID]);

    foreach ($roles AS $role) {
        $data = ['departmentID' => $departmentID, 'gibbonRoleID' => $role];
        $departmentPermissionsGateway->insert($data);
    }
    $URL .= '&return=success0';
    header("Location: {$URL}");
    exit();
}
?>
