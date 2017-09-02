app.controller("formManagerCtrl",['RQ','$scope','$routeParams',function(RQ,$scope,$routeParams){

    $scope.manager={pvlg:0};
    var getNum=0;
    var cover=function(){
        $scope.main.complete=false;
        getNum++;
    }
    var uncover=function(){
        getNum--;
        if (getNum==0) {
            $scope.main.complete=true;
        }
    }

    //=========================================================
    var subm=function(){
        cover();
        RQ.post(RQ.host + 'manager/man-'+($routeParams.id>-1?'upd':'add'), $scope.manager, function(res) {
            uncover();
            res=res.msg;
            if (res.code == 0) {
                alert("提交成功！");
                if ($routeParams.id>-1) {
                    window.location.reload();
                }else{
                    window.location="#/formManager/"+res.data.id;
                }
            }else{
                alert(res.info);
            }
        });
    }

    $scope.veriPvlg=function(idn){
        return ($scope.manager.pvlg&1)||($scope.manager.pvlg&idn);
    }

    $scope.chgPvlg=function(idn){
        $scope.manager.pvlg^=idn;
    }

    $scope.pwdchgBtn=function(){
        $scope.pwdchg=!$scope.pwdchg;
        if(!$scope.pwdchg)$scope.manager.password=$scope.opwd;
        else $scope.manager.password="";
    }

    $scope.pwdchgMsg=function(){
        return $scope.pwdchg?"取消修改":"修改密码";
    }

    $scope.submit=function(){
        subm();
    }

    $scope.init=function(){
        if ($routeParams.id>-1) {
            updateInit();
        }else{
            addInit();
        }
    }

    var addInit=function(){    
         $scope.pwdchg=true;
    }

    var updateInit = function() {
        $scope.pwdchg=false;
        cover();
        RQ.get(RQ.host + 'manager/man-detail', {
            "id": $routeParams.id
        }, function(res) {
            res=res.msg;
            if (res.code == 0) {
                $scope.manager=res.data.manager;
                $scope.opwd=$scope.manager.password;
            } else {
                alert(res.info);
            }
            uncover();
        });
    }

    $scope.goback=function(){
        window.location="#/tableManagers";
    }
    
}]);