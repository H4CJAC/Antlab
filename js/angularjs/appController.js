app.controller("indexCtrl", ['$scope', 'RQ', function($scope, RQ) {

	$scope.cmi=0;

	$scope.banners=[];
	$scope.memPNo=1;
	$scope.memPSize=3;
	$scope.memTCount=0;

	var getBanners=function(){
		RQ.get(RQ.host+"banner/list",{
			pageNo:1,
			pageSize:5
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.banners=res.data.banners;
				setTimeout("enableFlexSlider()",500);
			}else{
				alert(res.info);
			}
		});
	}

	var getIntro=function(){
		RQ.get(RQ.host+"intro/detail",{
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.intro=res.data.intro;
			}else{
				alert(res.info);
			}
		});
	}

	var getAnnos=function(){
		RQ.get(RQ.host+"anno/list",{
			pageNo:1,
			pageSize:6
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.annos=res.data.annos;
				for(x in $scope.annos){
					var dt=new Date();
					dt.setTime(Date.parse($scope.annos[x].data.pubtime));
					$scope.annos[x].data.month=monthToEN(dt.getMonth()+1);
					$scope.annos[x].data.date=dt.getDate();
				}
			}else{
				alert(res.info);
			}
		});
	}

	var getMembers=function(){
		RQ.get(RQ.host+"member/list",{
			pageNo:1,
			pageSize:15
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.members=res.data.members;
				$scope.memTCount=res.data.totalCount;
				setTimeout("enableOwlCarousel()",500);
				setTimeout("initMemPic()",500);
			}else{
				alert(res.info);
			}
		});
	}

	$scope.init=function(){
		getBanners();
		getIntro();
		getAnnos();
		getMembers();
	}

	$scope.urlto=function(type,oid){
		switch(type){
			case 1:
				window.location="annocontent.html?id="+oid;
				break;
			case 2:
				window.location="actcontent.html?id="+oid;
				break;
			default:
				break;
		}
	}

}]);

app.controller("headerCtrl", ['$scope', 'RQ', function($scope, RQ) {

	var getResearchs=function(){
		RQ.get(RQ.host+"research/list-ts",{
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.researchs=res.data.researchs;
			}else{
				alert(res.info);
			}
		});
	}

	$scope.init=function(){
		getResearchs();
	}

}]);

app.controller("introCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=1;

	var getIntro=function(){
		RQ.get(RQ.host+"intro/detail",{
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.intro=res.data.intro;
			}else{
				alert(res.info);
			}
		});
	}

	$scope.init=function(){
		getIntro();
	}

}]);

app.controller("teamCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=2;

	var getMembers=function(type,memtype){
		RQ.get(RQ.host+"member/list-all",{
			type:type
		},function(res){
			res=res.msg;
			if (res.code==0) {
				memtype.members=res.data.members;
				setTimeout("initMemPic()",500);
			}else{
				alert(res.info);
			}
		});
	}

	var getMemtypes=function(){
		RQ.get(RQ.host+"memtype/list-all",{
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.memtypes=res.data.memtypes;
				for(x in $scope.memtypes){
					$scope.memtypes[x].members=[];
					getMembers($scope.memtypes[x].id,$scope.memtypes[x]);
				}
			}else{
				alert(res.info);
			}
		});
	}

	$scope.detail=function(id){
		window.location="teammem.html?id="+id;
	}

	$scope.init=function(){
		getMemtypes();
	}
	
}]);

app.controller("teammemCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=2;

	var getMember=function(id){
		RQ.get(RQ.host+"member/detail",{
			id:id
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.member=res.data.member;
			}else{
				alert(res.info);
			}
		});
	}

	$scope.init=function(){
		getMember(RQ.getUrlParam("id"));
	}
	
}]);

app.controller("announceCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=3;

	$scope.pageNo=1;
	$scope.pageSize=5;
	$scope.totalCount=0;
	$scope.btns=[];
	$scope.btnmaxnum=10;

	var getAnnos=function(){
		RQ.get(RQ.host+"anno/list",{
			pageNo:$scope.pageNo,
			pageSize:$scope.pageSize
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.annos=res.data.annos;
				$scope.totalCount=res.data.totalCount;
				$scope.btns=getBtnArr($scope.totalCount,$scope.btnmaxnum,$scope.pageNo,$scope.pageSize);
				for(x in $scope.annos){
					var dt=new Date();
					dt.setTime(Date.parse($scope.annos[x].data.pubtime));
					$scope.annos[x].data.month=monthToEN(dt.getMonth()+1);
					$scope.annos[x].data.date=dt.getDate();
				}
				setTimeout("enableFlexSlider()",500);
			}else{
				alert(res.info);
			}
		});
	}

	$scope.init=function(){
		getAnnos();
	}

	$scope.jumpto=function(pn){
		if (pn!=$scope.pageNo) {
			$scope.pageNo=pn;
			getAnnos();
		}
	}

	$scope.front=function(){
		if ($scope.pageNo>1) {
			$scope.jumpto($scope.pageNo-1);
		}
	}

	$scope.back=function(){
		if ($scope.pageNo<$scope.btns.length) {
			$scope.jumpto($scope.pageNo+1);
		}
	}

}]);

app.controller("annocontentCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=3;

	var addView=function(id){
		RQ.get(RQ.host+"anno/view",{
			id:id
		},function(res){
		});
	}

	var getDetail=function(){
		RQ.get(RQ.host+"anno/detail",{
			id:RQ.getUrlParam("id")
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.anno=res.data.anno;
				var dt=new Date();
				dt.setTime(Date.parse($scope.anno.pubtime));
				$scope.anno.month=monthToEN(dt.getMonth()+1);
				$scope.anno.date=dt.getDate();
				addView($scope.anno.id);
			}else{
				alert(res.info);
			}
		});
	}

	var getPics=function(){
		RQ.get(RQ.host+"pic/list",{
			oid:RQ.getUrlParam("id"),
			type:1
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.pics=res.data.pics;
				setTimeout("enableFlexSlider()",500);
			}else{
				alert(res.info);
			}
		});
	}

	$scope.init=function(){
		getDetail();
		getPics();
	}
}]);

app.controller("researchCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=4;

	var addView=function(id){
		RQ.get(RQ.host+"research/view",{
			id:id
		},function(res){
		});
	}

	var getDetail=function(){
		RQ.get(RQ.host+"research/detail",{
			id:RQ.getUrlParam("id")
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.research=res.data.research;
				var dt=new Date();
				dt.setTime(Date.parse($scope.research.pubtime));
				$scope.research.month=monthToEN(dt.getMonth()+1);
				$scope.research.date=dt.getDate();
				addView($scope.research.id);
			}else{
				alert(res.info);
			}
		});
	}

	$scope.init=function(){
		getDetail();
	}
}]);

app.controller("paperCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=4;

	var addView=function(id){
		RQ.get(RQ.host+"paper/view",{
			id:id
		},function(res){
		});
	}

	$scope.getDetail=function(id){
		if(id<1){
			$scope.paper={name:"全部"};
			return;
		}
		RQ.get(RQ.host+"paper/detail",{
			id:id
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.curpt=id;
				$scope.paper=res.data.paper;
				var dt=new Date();
				dt.setTime(Date.parse($scope.paper.pubtime));
				$scope.paper.month=monthToEN(dt.getMonth()+1);
				$scope.paper.date=dt.getDate();
				addView($scope.paper.id);
			}else{
				alert(res.info);
			}
		});
	}

	var getTs=function(){
		RQ.get(RQ.host+"paper/list-ts",{
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.paperts=$scope.paperts.concat(res.data.papers);
			}else{
				alert(res.info);
			}
		});
	}

	$scope.init=function(){
		$scope.curpt=0;
		$scope.paperts=[{id:0,name:"全部"}];
		$scope.paper={name:"全部"};
		getTs();
	}
}]);


app.controller("activityCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=5;

	$scope.pageNo=1;
	$scope.pageSize=5;
	$scope.totalCount=0;
	$scope.btns=[];
	$scope.btnmaxnum=10;

	var getActs=function(){
		RQ.get(RQ.host+"activity/list",{
			pageNo:$scope.pageNo,
			pageSize:$scope.pageSize
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.acts=res.data.activitys;
				$scope.totalCount=res.data.totalCount;
				$scope.btns=getBtnArr($scope.totalCount,$scope.btnmaxnum,$scope.pageNo,$scope.pageSize);
				for(x in $scope.acts){
					var dt=new Date();
					dt.setTime(Date.parse($scope.acts[x].data.pubtime));
					$scope.acts[x].data.month=monthToEN(dt.getMonth()+1);
					$scope.acts[x].data.date=dt.getDate();
				}
				setTimeout("enableFlexSlider()",500);
			}else{
				alert(res.info);
			}
		});
	}

	$scope.init=function(){
		getActs();
	}

	$scope.jumpto=function(pn){
		if (pn!=$scope.pageNo) {
			$scope.pageNo=pn;
			getActs();
		}
	}

	$scope.front=function(){
		if ($scope.pageNo>1) {
			$scope.jumpto($scope.pageNo-1);
		}
	}

	$scope.back=function(){
		if ($scope.pageNo<$scope.btns.length) {
			$scope.jumpto($scope.pageNo+1);
		}
	}
}]);

app.controller("actcontentCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=5;

	var addView=function(id){
		RQ.get(RQ.host+"activity/view",{
			id:id
		},function(res){
		});
	}

	var getDetail=function(){
		RQ.get(RQ.host+"activity/detail",{
			id:RQ.getUrlParam("id")
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.act=res.data.activity;
				var dt=new Date();
				dt.setTime(Date.parse($scope.act.pubtime));
				$scope.act.month=monthToEN(dt.getMonth()+1);
				$scope.act.date=dt.getDate();
				addView($scope.act.id);
			}else{
				alert(res.info);
			}
		});
	}

	var getPics=function(){
		RQ.get(RQ.host+"pic/list",{
			oid:RQ.getUrlParam("id"),
			type:2
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.pics=res.data.pics;
				setTimeout("enableFlexSlider()",500);
			}else{
				alert(res.info);
			}
		});
	}

	$scope.init=function(){
		getDetail();
		getPics();
	}
}]);

app.controller("stuCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=6;

	$scope.pageNo=1;
	$scope.pageSize=15;
	$scope.totalCount=0;
	$scope.btns=[];
	$scope.btnmaxnum=10;

	$scope.jumpto=function(pn){
		if (pn!=$scope.pageNo) {
			$scope.pageNo=pn;
			getList();
		}
	}

	$scope.front=function(){
		if ($scope.pageNo>1) {
			$scope.jumpto($scope.pageNo-1);
		}
	}

	$scope.back=function(){
		if ($scope.pageNo<$scope.btns.length) {
			$scope.jumpto($scope.pageNo+1);
		}
	}

	var getList=function(){
		$scope.adding=60;
		RQ.get(RQ.host+"student/list",{
			pageNo:$scope.pageNo,
			pageSize:$scope.pageSize
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.adding=100;
				$scope.stus=res.data.stus;
				$scope.totalCount=res.data.totalCount;
				$scope.btns=getBtnArr($scope.totalCount,$scope.btnmaxnum,$scope.pageNo,$scope.pageSize);
				$scope.adding=0;
			}else{
				alert(res.info);
			}
		});
	}

	$scope.addStu=function(){
		$scope.adding=20;
		RQ.post(RQ.host+"student/add",$scope.stu,function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.adding=60;
				getList();
				alert("登记成功！");
			}else{
				alert(res.info);
				$scope.adding=0;
			}
		});
	}

	$scope.init=function(){
		$scope.adding=0;
		$scope.stu={};
		getList();
	}
}]);

app.controller("stuFileCtrl", ['$scope', 'RQ', function($scope, RQ) {
	$scope.cmi=6;

	$scope.pageNo=1;
	$scope.pageSize=15;
	$scope.totalCount=0;
	$scope.btns=[];
	$scope.btnmaxnum=10;

	$scope.jumpto=function(pn){
		if (pn!=$scope.pageNo) {
			$scope.pageNo=pn;
			getList();
		}
	}

	$scope.front=function(){
		if ($scope.pageNo>1) {
			$scope.jumpto($scope.pageNo-1);
		}
	}

	$scope.back=function(){
		if ($scope.pageNo<$scope.btns.length) {
			$scope.jumpto($scope.pageNo+1);
		}
	}

	var getList=function(){
		$scope.adding=60;
		RQ.get(RQ.host+"student/file-list",{
			pageNo:$scope.pageNo,
			pageSize:$scope.pageSize
		},function(res){
			res=res.msg;
			if (res.code==0) {
				$scope.adding=100;
				$scope.stuFiles=res.data.stuFiles;
				$scope.totalCount=res.data.totalCount;
				$scope.btns=getBtnArr($scope.totalCount,$scope.btnmaxnum,$scope.pageNo,$scope.pageSize);
				$scope.adding=0;
			}else{
				alert(res.info);
			}
		});
	}

	$scope.addStuFile=function(){
		if(!$scope.sid>0){
			alert("学号不可为空！");
			return false;
		}
		$scope.adding=20;

        var files=$("#inputfile")[0].files;
        if(files!=undefined&&files.length>0){
        	var fd=new FormData();
        	fd.append("StuUploadForm[upFiles][]",files[0]);
        	RQ.uploadfileWithParams(RQ.host+"student/upload",fd,{
        		"sid":$scope.sid,
        		"remark":$scope.remark
        	},function(res){
				res=res.msg;
				if (res.code==0) {
					$scope.adding=60;
					getList();
					alert("上传成功！");
				}else{
					alert(res.info);
					$scope.adding=0;
				}
			});
        }else{
        	alert("文件不可为空！");
        	$scope.adding=0;
        }
		
	}

	$scope.init=function(){
		$scope.adding=0;
		$scope.stu={};
		getList();
	}
}]);

var initMemPic=function(){
	var mpics=$(".mempic");
	for(var x=0;x<mpics.length;x++)mpics[x].height=mpics[x].width;
}

function getBtnArr(totalCount,btnmaxnum,pageNo,pageSize){
	var arr=[{pn:1}];
	if (totalCount>0) {
		var btnnum=Math.ceil(totalCount/pageSize);
		if (btnnum<=btnmaxnum) {
			for (var i = 0; i <btnnum; i++) {
				arr[i]={pn:i+1};
			}
		}else{
			var start;
			if(pageNo<=btnmaxnum/2){
				start=1;
			}else if(pageNo+btnmaxnum/2>=btnnum){
				start=btnnum-btnmaxnum+1;
			}else{
				start=pageNo-Math.ceil(btnmaxnum/2)+1;
			}
			for(var i=0;i<btnmaxnum;i++){
				arr[i]={pn:i+start}
			}
		}
	}
	return arr;
}


/* Flex Slider */
function enableFlexSlider(){



	// Main Flexslider
	$('.main-flexslider').flexslider({
		animation: "slide",
		controlNav: false,
		prevText: "",           
		nextText: "", 
	});




	// Banner Rotator
	$('.banner-rotator-flexslider').flexslider({
		animation: "slide",
		controlNav: true,
		directionNav: false,
		prevText: "",           
		nextText: "", 
	});



	// Portfolio Slideshow
	$('.portfolio-slideshow').flexslider({
		animation: "fade",
		controlNav: false,
		slideshowSpeed: 4000,
		prevText: "",           
		nextText: "", 
	});

}

function monthToEN(month){
	switch(month){
		case 1:
			return "JAN";
		case 2:
			return "FEB";
		case 3:
			return "MAR";
		case 4:
			return "APR";
		case 5:
			return "MAY";
		case 6:
			return "JUN";
		case 7:
			return "JUL";
		case 8:
			return "AUG";
		case 9:
			return "SEP";
		case 10:
			return "OCT";
		case 11:
			return "NOV";
		case 12:
			return "DEC";
		default:
			return "";
	}
}

/* Owl Carousel */
function enableOwlCarousel(){

	$('.owl-carousel').each(function(){
		
		/* Number Of Items */
		var max_items = $(this).attr('data-max-items');
		var tablet_items = max_items;
		if(max_items > 1){
			tablet_items = max_items - 1;
		}
		var mobile_items = 1;


		/* Initialize */
		$(this).owlCarousel({
			items:max_items,
			pagination : false,
			itemsDesktop : [1600,max_items],
			itemsDesktopSmall : [1170,max_items],
			itemsTablet: [991,tablet_items],
			itemsMobile: [767,mobile_items],
			slideSpeed:400
		});
		

		var owl = $(this).data('owlCarousel');

		// Left Arrow
		$(this).parent().find('.carousel-arrows span.left-arrow').click(function(e){
			owl.prev();
		});

		// Right Arrow
		$(this).parent().find('.carousel-arrows span.right-arrow').click(function(e){
			owl.next(); 
		});

	});


}


function safeStr(str){
	return str.replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}
