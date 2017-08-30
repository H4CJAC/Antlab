app.controller("formMemberCtrl",['FileUploader','RQ','$scope','$routeParams',function(FileUploader,RQ,$scope,$routeParams){

    $scope.member={};
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
    var subm=function(ourl,nurl){
        cover();
        $scope.member.intro=document.getElementById("editor2").innerHTML;
        $scope.member.coverpic=nurl;
        RQ.post(RQ.host + 'member/man-'+($routeParams.id>-1?'upd':'add'), $scope.member, function(res) {
            uncover();
            res=res.msg;
            if (res.code == 0) {
                alert("提交成功！");
                if ($routeParams.id>-1) {
                    if(ourl!=null){
                        delf(ourl);
                    }
                    window.location.reload();
                }else{
                    window.location="#/formMember/"+res.data.id;
                }
            }else{
                alert(res.info);
            }
        });
    }

    var delf=function(url){
        cover();
        RQ.get(RQ.host + 'pic/man-del-w-u', {
            "url":url
        }, function(res) {
            uncover();
            res=res.msg;
            if (res.code == 0) {
            } else {
                alert(res.info);
            }
        });
    }

    var uppic=function(oid){
        var imgs=Dropzone.instances[0].files;
        if (imgs!=undefined&&imgs.length>0) {
            if(imgs.length>1){
                alert("最多上传一张图片！");
                return;
            }
            cover();
            var fd=new FormData();
            fd.append("UploadForm[imageFiles][]",imgs[0]);
            RQ.uploadfileWithParams(RQ.host+"pic/man-upload",fd,{
            },function(res){
                res=res.msg;
                uncover();
                if (res.code == 0) {
                    subm($scope.member.coverpic,res.data[0]);
                } else {
                    alert(res.info);
                }
            });
        }else{
            subm(null,$scope.member.coverpic);
        }
    }

    $scope.submit=function(){
        uppic();
    }

    $scope.init=function(){
        getMemtypes();
        if ($routeParams.id>-1) {
            updateInit();
            // getPics();
        }else{
            addInit();
        }
    }

    var addInit=function(){    
         
    }

	var updateInit = function() {
        cover();
		RQ.get(RQ.host + 'member/man-detail', {
			"id": $routeParams.id
		}, function(res) {
            res=res.msg;
			if (res.code == 0) {
				$scope.member=res.data.member;
                document.getElementById("editor2").innerHTML=$scope.member.intro;
            } else {
                alert(res.info);
            }
            uncover();
        });
	}

    var getMemtypes = function() {
        cover();
        RQ.get(RQ.host + 'memtype/man-list-all', {
        }, function(res) {
            res=res.msg;
            if (res.code == 0) {
                $scope.memtypes=res.data.memtypes;
            } else {
                alert(res.info);
            }
            uncover();
        });
    }

    var getPics = function() {
        cover();
        RQ.get(RQ.host + 'pic/man-list', {
            "oid": $routeParams.id,
            "type":2
        }, function(res) {
            res=res.msg;
            if (res.code == 0) {
                $scope.pics=res.data.pics;
            } else {
                alert(res.info);
            }
            uncover();
        });
    }

    $scope.goback=function(){
        window.location="#/tableMembers";
    }

    var dz=function(){
        try {
            Dropzone.autoDiscover = false;
            var myDropzone;
            if(Dropzone!=undefined&&Dropzone.instances.length>0){
                Dropzone.instances=[];
            }

            myDropzone = new Dropzone("#dropzone" , {
                paramName: "file", // The name that will be used to transfer the file
                maxFilesize: 50, // MB
                thumbnailWidth:500,
                thumbnailHeight:500,

                addRemoveLinks : true,
                dictDefaultMessage :
                '<span class="bigger-150 bolder"><i class="ace-icon fa fa-caret-right red"></i> Drop files</span> to upload \
                <span class="smaller-80 grey">(or click)</span> <br /> \
                <i class="upload-icon ace-icon fa fa-cloud-upload blue fa-3x"></i>'
                ,
                dictResponseError: 'Error while uploading file!',
                
                
                previewTemplate: "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-details\">\n    <div class=\"dz-filename\"><span data-dz-name></span></div>\n    <div class=\"dz-size\" data-dz-size></div>\n    <img data-dz-thumbnail />\n  </div>\n   <div class=\"dz-success-mark\"><span></span></div>"
            });

            $(document).one('ajaxloadstart.page', function(e) {
                try {
                    myDropzone.destroy();
                } catch(e) {}
            });

        } catch(e) {
            alert(e);
        }

    }
    $scope.wysclear=function(){
        document.getElementById("editor2").innerHTML="";
    }
    var wys=function(){
        $('#editor2').css({'height':'200px'}).ace_wysiwyg({
            toolbar_place: function(toolbar) {
                return $(this).closest('.widget-box')
                       .find('.widget-header').prepend(toolbar)
                       .find('.wysiwyg-toolbar').addClass('inline');
            },
            toolbar:
            [
                'font',
                null,
                'fontSize',
                null,
                'bold',
                'italic',
                'strikethrough',
                'underline',
                null,
                'insertunorderedlist',
                'insertorderedlist',
                'outdent',
                'indent',
                null,
                'justifyleft',
                'justifycenter',
                'justifyright',
                'justifyfull',
                null,
                'createLink',
                'unlink',
                null,
                null,
                null,
                'foreColor',
                null,
                'undo',
                'redo',
                null,
                'viewSource'
            ],
            speech_button: false
        });
    }
    wys();
    dz();

	
}]);