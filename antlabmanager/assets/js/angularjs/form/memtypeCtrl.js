app.controller("formMemtypeCtrl",['FileUploader','RQ','$scope','$routeParams',function(FileUploader,RQ,$scope,$routeParams){

    $scope.memtype={isshow:0,showpic:0};
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
        RQ.post(RQ.host + 'memtype/man-'+($routeParams.id>-1?'upd':'add'), $scope.memtype, function(res) {
            uncover();
            res=res.msg;
            if (res.code == 0) {
                alert("提交成功！");
                if ($routeParams.id>-1) {
                    window.location.reload();
                }else{
                    window.location="#/formMemtype/"+res.data.id;
                }
            }else{
                alert(res.info);
            }
        });
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
         
    }

	var updateInit = function() {
        cover();
		RQ.get(RQ.host + 'memtype/man-detail', {
			"id": $routeParams.id
		}, function(res) {
            res=res.msg;
			if (res.code == 0) {
				$scope.memtype=res.data.memtype;
            } else {
                alert(res.info);
            }
            uncover();
        });
	}


    $scope.goback=function(){
        window.location="#/tableMemtypes";
    }


	
}]);