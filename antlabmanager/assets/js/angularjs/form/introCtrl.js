app.controller("formIntroCtrl",['FileUploader','RQ','$scope','$routeParams',function(FileUploader,RQ,$scope,$routeParams){

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
        $scope.intro.content=document.getElementById("editor2").innerHTML;
        RQ.post(RQ.host + 'intro/man-upd', $scope.intro, function(res) {
            uncover();
            res=res.msg;
            if (res.code == 0) {
                alert("提交成功！");
                window.location.reload();
            }else{
                alert(res.info);
            }
        });
    }

    $scope.submit=function(){
        subm();
    }

    $scope.init=function(){
        updateInit();
    }

    var updateInit = function() {
        cover();
        RQ.get(RQ.host + 'intro/man-detail', {
            "id": 1
        }, function(res) {
            res=res.msg;
            if (res.code == 0) {
                $scope.intro=res.data.intro;
                document.getElementById("editor2").innerHTML=$scope.intro.content;
            } else {
                alert(res.info);
            }
            uncover();
        });
    }


    $scope.goback=function(){
        window.location="#/";
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


    
}]);