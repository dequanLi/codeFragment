(function(){  
    /** 
    *dqKeys v1.0.0 | (c) 2016  www.findme.wang 
    *@params json keys  监听的按键 
    *@params bool isOrder  按键是否有相应的顺序 
    *@params Function sucFuc  完成按键的回调函数 
    *@params Function cancelFuc  完成按键取消后的回调函数 
    *@author lidequan  
    */  
    var dqKeys = function(keys,isOrder,sucFuc,cancelFuc) {  
        // 函数体  
        return new dqKeys.fn.init(keys,isOrder,sucFuc,cancelFuc);  
    }  
    dqKeys.fn = dqKeys.prototype = {  
        'version':'1.0.0',    //版本号  
        'author':'lidequan',  //作者  
        'rightKeys':{},      //监听的按键{key:code},code为按键对应的ascii码   
        'curKeys':[],        //当前按下的键  
        'sucFuc':null,       //完成按键的回调函数  
        'cancelFuc':null,    //完成按键取消后的回调函数  
        'isFinsh':false,    //判断是否完成按键  
        'isOrder':false,    //按键是否有相应的顺序  
        init:function(keys,isOrder,sucFuc,cancelFuc){  
            this.rightKeys=keys;  
            this.sucFuc=sucFuc;  
            this.cancelFuc=cancelFuc;  
            this.isOrder=isOrder;  
              
            return this;  
        },  
        listenkeys:function(){//监听用户键盘操作                  
            var _self=this;  
            _self.addListener('keydown', function(oEvent){  
                var oEvent =oEvent || window.event;  
                if(!_self.arrayContain(_self.curKeys,oEvent.keyCode)){  
                    if(_self.isOrder && _self.getNextKey() == oEvent.keyCode){  
                        _self.curKeys.push(oEvent.keyCode);  
                    }else if(!_self.isOrder){  
                        _self.curKeys.push(oEvent.keyCode);  
                    }  
                }  
                if(_self.checkResult(_self.rightKeys,_self.curKeys)){  
                    if(_self.sucFuc && !_self.isFinsh){  
                        _self.sucFuc();  
                    }  
                    _self.isFinsh=true;  
                }  
            });  
            _self.addListener('keyup', function(oEvent){  
                var oEvent =oEvent || window.event;                   
                if(_self.checkResult(_self.rightKeys,_self.curKeys) && _self.isFinsh){        
                    //完成按键,又取消的事件  
                    if(_self.cancelFuc){  
                        _self.cancelFuc();  
                    }  
                }  

                _self.curKey=_self.remove(_self.curKeys,oEvent.keyCode);  
                _self.isFinsh=false;  
            });  
        },  
        arrayContain:function(arr,val){//判断数组中是否包含某个元素  
                return (arr.indexOf(val) == -1) ? false:true;  
        },  
        checkResult:function(json,arr){ //判断用户是否按下监听的所有按键  
            for(var i in json){  
                 if(arr.indexOf(json[i])==-1){  
                     return false;  
                 }  
             }  
            return true;  
        },  
        remove:function(arr,val) {  //从数组中移除某个元素              
            var index = arr.indexOf(val);   
            if (index > -1) {   
                arr.splice(index, 1);   
            }   
            return arr;  
        },  
        getNextKey:function(){ //获取下一次按键对应的ascii码  
            for(var i in this.rightKeys){  
                if(this.curKeys.indexOf(this.rightKeys[i])==-1){  
                     return this.rightKeys[i];  
                 }  
            }  
            return null;  
        },  
        addListener:function(ev,fn,bool){  //事件绑定  
            if (document.attachEvent) {    
                document.attachEvent('on' + ev, fn);    
            }else{     
                document.addEventListener(ev,fn,bool);    
            }    
        }  
    }  
    dqKeys.fn.init.prototype = dqKeys.fn;  
    window.dqKeys = window.$$= dqKeys;  
})();  
  



/**
*使用说明
*只需要引进dqKeys.js即可，不依赖其他库文件
*
//1.测试  ,监听 a,b 且有相应的顺序，完成时输出okey,否则输出cacel
dqKeys({'a':65,'b':66},true,function(){  
                                console.log('okey');  
                            },function(){  
                                console.log('cancel');  
                            }).listenkeys();  

//2.测试  ,监听 c,d 不考虑顺序，完成时输出keys down,否则输出keys cancel
var dqKeys=new $$({'c':67,'d':68},false,function(){  
                                console.log('keys down ');  
                            },function(){  
                                console.log('keys cancel');  
                            });  
dqKeys.listenkeys();  
/