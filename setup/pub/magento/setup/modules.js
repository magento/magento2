/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

'use strict';
angular.module('modules', ['ngStorage'])
    .controller('modulesController', ['$scope', '$localStorage' , '$http', function ($scope, $localStorage, $http) {
        $scope.list = [];
        $scope.checked = [];

        $scope.addColumn = function(mycolumn) {
            var addToArray=true;
            for(var i=0;i<$scope.list.length;i++){
                if($scope.list[i].column===mycolumn){
                    addToArray=false;
                        break;
                }
            }
            if(addToArray){
                $scope.list.push({column: mycolumn, modules: []});
            }
        }

        $scope.add = function(value, select, mvp, $mycolumn) {
            var addToArray=true;
            for(var i=0;i<$scope.list.length;i++){
                for(var j=0;j<$scope.list[i].modules.length;j++){
                    if($scope.list[i].modules[j].label===value){
                        addToArray=false;
                        break;
                    }
                }
            }
            if(addToArray){
                $scope.list[$mycolumn].modules.push({label: value, select: select, mvp: mvp});
            }
        };

        $scope.verifyDependencies = function()
        {
            alert();
        }

//        if ($localStorage.checked) {
//            $scope.checked = $localStorage.checked;
//        }
//
//        $scope.$on('nextState', function () {
//            alert($localStorage.checked);
//            $localStorage.checked = function(){
//                $scope.modules($scope.modules.select);
//            }
//
//        });
    }]);
