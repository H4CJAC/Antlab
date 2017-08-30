app.service('RQ', ['$q','$http', '$location', function($q,$http, $location) {

    this.host = 'http://localhost/nowecake/antlabserver/basic/web/';

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

    this.get = function(url, rq, cb) {
        url = (url.indexOf('?') > -1) ? url + '&' + $.param(rq) : url + '?' + $.param(rq)
        $http.get(url).success(function(d) {
            cb(d)
        })
    }

    this.post = function(url, rq,cb) {
        $http.post(url, rq).success(function(d) {
            cb(d)
        })
    }

    this.getUrlParam = function(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]);
        return null;
    }

    this.uploadfileWithParams=function(url,fd,rq,cb){
        if (!$.isEmptyObject(rq)) {
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

}]);

