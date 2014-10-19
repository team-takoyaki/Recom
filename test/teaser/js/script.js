(function () {


$('section.image_area .android').toggle(
    function () {
        $(this).css({'-webkit-transform' : 'rotate(18deg)'});        
    },
    function () {
        $(this).css({'-webkit-transform' : 'rotate(22deg)'});        
    }
);



var timer = 1000;
setTimeout (function () {
    var event = $('section.image_area .event');
    var current = event.find('.current')[0];
//    console.dir(event);    
    console.dir(current);
    $(current).removeClass('current').next().eq(0).addClass('current');
    
}, timer);


/*
var arc_params = {
    center: [300,200],  
    radius: 150,
    start: 180,
    end: 0,
    dir: -1
};

var arc_params2 = {
    center: [600,150],
    radius: 350,
    start: -60,
    end: 10,
    dir: 1
};    
    
event.eq(0).css({'display':'block'})
        .animate({'bottom':'450px'}, 300, 'swing')
        .animate({path : new $.path.arc(arc_params)}, 600)
        .animate({path : new $.path.arc(arc_params2)}, 900)
        .fadeTo("fast", 0);
*/    
})();
