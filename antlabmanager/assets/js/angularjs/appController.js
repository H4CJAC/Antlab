
app.controller("mainCtrl",['$scope','RQ',function($scope,RQ){
    $scope.main={};
    $scope.menuShow=false;
    $scope.main.complete=true;
    $scope.nickname=RQ.getCookie("nickname");
    $scope.getTitle=function(){
        var hashurl= window.location.hash.split("/",2);
        if (hashurl.length>1) {
            var title= hashurl[1].replace(/(table|form)/,"");
            title=title.split("");
            if (title[title.length-1]=="s") {
                title.pop();
            };
            switch(title.join("")){
                case "Activity":
                    return "活动管理";
                case "Anno":
                    return "通知管理";
                case "Research":
                    return "研究成果管理";
                case "Memtype":
                    return "成员类型管理";
                case "Member":
                    return "成员管理";
                case "Intro":
                    return "简介管理";
                case "Manager":
                    return "管理员管理";
                default: 
                    return "";
            }
        }else{
            return "";
        }
    }

    $scope.menuHide=function(){
        $scope.menuShow=false;
    }

    $scope.logout=function(){
        RQ.get(RQ.host + 'manager/logout', {
        }, function(res) {
            res=res.msg;
            if (res.code == 0) {
                RQ.delCookie("at_token");
                window.location="login.html";
            } else {
                alert(res.info);
            }
        });
    }
}]);

app.controller("indexCtrl",['$scope','RQ',function($scope,RQ){
    $scope.main.complete=true;
}]);

app.controller("loginCtrl",['$scope','RQ',function($scope,RQ){
    $scope.rem=true;
    $scope.manager={};
    var t;
    $scope.manager.username=(t= RQ.getCookie("at_uname"))==undefined?"":t;
    $scope.manager.password=(t= RQ.getCookie("at_pwd"))==undefined?"":t;

    $scope.login=function(){
        if ($scope.rem) {
            RQ.setCookie("at_uname",$scope.manager.username,365);
            RQ.setCookie("at_pwd",$scope.manager.password,365);
        }else{
            RQ.delCookie("at_uname");
            RQ.delCookie("at_pwd");
        }
        RQ.post(RQ.host + 'manager/login', 
        {
            username:$scope.manager.username,
            password:$scope.manager.password
        }, 
        function(res) {
            res=res.msg;
            if (res.code == 0) {
                RQ.setCookie("at_token",res.data.at_token,7);
                RQ.setCookie("nickname",res.data.name,7);
                window.location="./#/";
            } else {
                $scope.msg=res.info;
            }
        });
    }
    $scope.close=function(){
        $scope.msg=null;
    }
    // $scope.main.complete=true;
}]);

var dateFormate=function(date){
	if (date!=undefined) {
		return date.replace(/ /,'T')+'.000+0800';
	};
	return date;
}

