(function () {

$('section.image_area').click(function () {
    $('.desc_slide p').trigger('click');
});

$('.desc_slide p').click(function(){
    $(this).hide();
    $('.desc_slide .slide').show();
    $($.browser.safari ? 'body' : 'html').animate({scrollTop:$('h1').offset().top}, 300, 'swing');
});

    

$('section.image_area .android').toggle(
    function () {
        $(this).css({'-webkit-transform' : 'rotate(18deg)'});
    },
    function () {
        $(this).css({'-webkit-transform' : 'rotate(22deg)'});        
    }
);



var timer = 3000;
function move_event () {
    var event = $('section.image_area .event');
    var current = event.find('.current')[0];

    var arc_params = {
        center: [300,80],
        radius: 100,
        start: 180,
        end: 0,
        dir: -1
    };
    
    var arc_params2 = {
        center: [600,0],
        radius: 350,
        start: -60,
        end: 10,
        dir: 1
    };    
    
    $(current).css({'display':'block'})
        .animate({'bottom':'400px'}, 300, 'swing')
        .delay(150)
        .animate({path : new $.path.arc(arc_params)}, 600)
        .animate({path : new $.path.arc(arc_params2)}, 900)
        .fadeTo("fast", 0, function () {
            $(this).css({'top':'', 'left':'300px', 'bottom':'-100px', 'opacity':1, 'display':'none'});
            $('section.image_area .android').trigger('click');
            random_face ();            
        });
    
    var next = $(current).removeClass('current').next();
    if (next.length != 0) {
        next.eq(0).addClass('current');
    } else {
        event.find('li').eq(0).addClass('current');
    }

    function random_face () {
        var faces = $('section.image_area .android li');
        var i = Math.floor( Math.random() * faces.length);
        faces.css({'display':'none'}).eq(i).css({'display':'inline'});
    }
    
    
    

    setTimeout (move_event, timer);
}

   
move_event();

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
