

app.service('RQ', ['$q','$http', '$location', function($q,$http, $location) {

    this.host = 'http://localhost/nowecake/antlabserver/basic/web/';

    this.orders=[];

    this.getCookie = function(name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        if (arr = document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
    }

    this.setCookie=function(c_name,value,expiredays){
        var exdate=new Date();
        exdate.setDate(exdate.getDate()+expiredays);
        document.cookie=c_name+ "=" +escape(value)+
        ((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
    }

    this.delCookie=function(c_name){
        this.setCookie(c_name,"",0);
    }

    this.at_token = this.getCookie('at_token');
    this.name=this.getCookie('nickname');
    if (this.at_token == null || this.at_token == '') {
        var url = $location.absUrl();
        if (!(url.indexOf('login.html') > 0)) {
            window.location = "login.html";
        }
    }

    this.get = function(url, rq, cb) {
        rq.at_token=this.at_token;
        if (!$.isEmptyObject(rq)) {
            rq.at_token=this.at_token;
            url = (url.indexOf('?') > -1) ? url + '&' + $.param(rq) : url + '?' + $.param(rq)
        }
        $http.get(url).success(function(d) {
            cb(d)
        })
    }

    this.post = function(url, rq, cb) {
        url+="?at_token="+this.at_token;
        $http.post(url, rq).success(function(d) {
            cb(d)
        })
    }

    this.uploadfile=function(url,fd,cb){
        url+="?at_token="+this.at_token;
        $http({
            method:"POST",
            url:url,
            data:fd,
            headers:{"Content-Type":undefined},
            transformRequest:angular.identity
        }).success(function(d){
            cb(d)
        });
    }

    this.uploadfileWithParams=function(url,fd,rq,cb){
        rq.at_token=this.at_token;
        if (!$.isEmptyObject(rq)) {
            rq.at_token=this.at_token;
            url = (url.indexOf('?') > -1) ? url + '&' + $.param(rq) : url + '?' + $.param(rq)
        }
        $http({
            method:"POST",
            url:url,
            data:fd,
            headers:{"Content-Type":undefined},
            transformRequest:angular.identity
        }).success(function(d){
            cb(d)
        });
    }


    this.getUrlParam = function(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]);
        return null;
    }

    this.dataUrl2Blob=function(dataUrl){
        var arr = dataUrl.split(','), mime = arr[0].match(/:(.*?);/)[1];
        var bytes=window.atob(arr[1]); 
        var ab = new ArrayBuffer(bytes.length);
        var ia = new Uint8Array(ab);
        for (var i = 0; i < bytes.length; i++) {
            ia[i] = bytes.charCodeAt(i);
        }
        return new Blob([ia], {type:mime});
    }


}]);

