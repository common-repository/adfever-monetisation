jQuery(document).ready(function($) {
	//console.log(ajax_object.ajaxurl);
	jQuery('.adfeverlinks').each(function() {
	    var divid = this.id;
	    var toRemove = 'adfever-';
	    var postid = divid.replace(toRemove,'');
	    
	    //console.log(postid);
	    
	    jQuery.ajax({
	        url: ajax_object.ajaxurl, // this is the object instantiated in wp_localize_script function
	        type: 'POST',
	        dataType: 'html',
	        data:{ 
	          action: 'AFajax',
	          options: ajax_object.AF_options,
	          theID: postid
	        },
	        success: function(data){
	          //Do something with the result from server
	          //console.log(data);
	          jQuery("div[id="+divid+"]").each(function() {
	          	jQuery(this).html(data);
	          });
	          //jQuery('#'+divid).html(data);
	          // console.log(data);
	        },
	        error: function(errorThrown){
	          //console.log(errorThrown);
	        }
	    });
	    
	    
	});
	
});
    
