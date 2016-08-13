$(function(){
    var path = this.location.pathname;
    $('#main-nav .nav a[href="' + path + '"]').parent().addClass('active');
});