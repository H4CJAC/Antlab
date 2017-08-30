app.controller("formAdverCtrl",['FileUploader','RQ','$scope','$routeParams',function(FileUploader,RQ,$scope,$routeParams){

	$scope.adver={position:0};
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
        RQ.post(RQ.host + 'manager/adver/'+($routeParams.id>-1?'upd':'add')+'.html', $scope.adver, function(res) {
            uncover();
            if (res.code == 0) {
                if (getNum==0) {
                    alert("提交成功！");
                    window.location="#/formAdver/"+res.data.id;
                };
            } else {
                alert(res.info);
            }
        });
    }

    var delf=function(url){
        RQ.get(RQ.host + 'manager/file/del.html', {
            "url":url
        }, function(res) {
            if (res.code == 0) {
            } else {
                alert(res.info);
            }
        });
    }

    $scope.submit=function(){
        var file=document.querySelector("input[type=file]").files[0];
        if (file!=undefined) {
            var fd=new FormData();
            cover();
            fd.append("uppic",file);
            RQ.uploadfile(RQ.host+"manager/pic/up.html",fd,function(res){
                if (res.code == 0) {
                    var opic=$scope.adver.pic;
                    $scope.adver.pic=res.data;
                    if (opic!=undefined) {
                        cover();
                        RQ.post(RQ.host + 'manager/adver/'+($routeParams.id>-1?'upd':'add')+'.html', $scope.adver, function(res) {
                            uncover();
                            if (res.code == 0) {
                                delf(opic);
                                if (getNum==0) {
                                    alert("提交成功！");
                                    window.location="#/formAdver/"+res.data.id;
                                };
                            } else {
                                alert(res.info);
                            }
                        });
                    }else{
                        subm();
                    }
                } else {
                    alert(res.info);
                }
                uncover();
            });
        }else{
            subm();
        }
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
		RQ.get(RQ.host + 'manager/adver/detail.html', {
			"id": $routeParams.id
		}, function(res) {
			if (res.code == 0) {
				$scope.adver=res.data;
            } else {
                alert(res.info);
            }
            uncover();
        });
	}

    $scope.goback=function(){
        window.location="#/tableAdvers";
    }



    $('#id-input-file-3').ace_file_input({
        style:'well',
        btn_choose:'Drop files here or click to choose',
        btn_change:null,
        no_icon:'ace-icon fa fa-cloud-upload',
        droppable:true,
        thumbnail:'fit'
        ,
        preview_error : function(filename, error_code) {
        }

    }).on('change', function(){
    });

	
}]);