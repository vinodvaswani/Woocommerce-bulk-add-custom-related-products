jQuery(document).ready(function(){


	/* For section 1 */
	jQuery("form#add-bulk").on('change',"#product_category",function(){
		var p_slug = jQuery(this).val();
		var this_elm = jQuery(this);
		if(p_slug=='')
		{
			jQuery("#products").html("Please select category to load products");
			return false;
		}

		this_elm.next("img").show();

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"p_slug":p_slug,"action":"load_products","section":1},
			type: "POST",
			success: function(result)
			{
        		jQuery("#products").html(result);
        		this_elm.next("img").hide();
    		},
    		complete: function(result)
    		{
    			jQuery("#selected-products li").each(function(i,e){
    				jQuery('#product-'+jQuery(this).attr('data-id')).attr('checked', true);
    			});
    			this_elm.next("img").hide();
    			jQuery('#select-related-products').show();
    		}
    	});
	});



	/* For section 2 */
	jQuery("form#add-bulk").on('change',"#product_category_2",function(){
		var p_slug_2 = jQuery(this).val();
		var this_elm = jQuery(this);

		if(p_slug_2=='')
		{
			jQuery("#products_2").html("Please select category to load products");
			return false;
		}

		this_elm.next("img").show();

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"p_slug":p_slug_2,"action":"load_products","section":2},
			type: "POST",
			success: function(result)
			{
        		jQuery("#products_2").html(result);
        		this_elm.next("img").hide();
    		},
    		complete: function(result)
    		{
    			jQuery("#selected-products-2 li").each(function(i,e){
    				jQuery('#product-2-'+jQuery(this).attr('data-id-2')).attr('checked', true);
    			});
    			this_elm.next("img").hide();
    			jQuery('#select-related-products-2').show();
    		}
    	});
	});


	/* For section 1 */
	jQuery("form#add-bulk").on('click','.bulk_product', function(){
		var bulk_product_id = jQuery(this).attr('data-id');
		var bulk_product_name = jQuery(this).attr('data-name');

			if(jQuery('li[data-id="'+bulk_product_id+'"]').length==0)
			jQuery('#selected-products').append('<li class="selected-item" data-id="'+bulk_product_id+'">'+bulk_product_name+'<span class="remove_elm"><img src="'+ajaxObject.remove_img+'"></span></li>');
			else
			jQuery('.selected-item[data-id="'+bulk_product_id+'"]').remove();


			jQuery('#selected-related-products').show();
	});



	/* For section 2 */
	jQuery("form#add-bulk").on('click','.bulk_product_2', function(){
		var bulk_product_id_2 = jQuery(this).attr('data-id-2');
		var bulk_product_name_2 = jQuery(this).attr('data-name-2');

			if(jQuery('li[data-id-2="'+bulk_product_id_2+'"]').length==0)
			jQuery('#selected-products-2').append('<li class="selected-item-2" data-id-2="'+bulk_product_id_2+'">'+bulk_product_name_2+'<span class="remove_elm_2"><img src="'+ajaxObject.remove_img+'"></span></li>');
			else
			jQuery('.selected-item-2[data-id-2="'+bulk_product_id_2+'"]').remove();

			jQuery('#selected-related-products-2').show();
	});



	/* Remove selected item from list and also uncheck checkbox  */
	jQuery("#selected-products").on('click', ".remove_elm", function(){
		var chk_id = jQuery(this).parent().attr('data-id');
		if(jQuery("#product-"+chk_id).length>0)
		{
			jQuery("#product-"+chk_id).attr('checked',false);
		}
		jQuery(this).parent('li').remove();	
		
	});


	jQuery("#selected-products-2").on('click', ".remove_elm_2", function(){
		var chk_id2 = jQuery(this).parent().attr('data-id-2');
		if(jQuery("#product-2-"+chk_id2).length>0)
		{
			jQuery("#product-2-"+chk_id2).attr('checked',false);	
		}
		jQuery(this).parent('li').remove();
	});

    

	jQuery(document).on('submit',"form#add-bulk",function(e){

		e.preventDefault();

		var product_ids = [];
		var related_product_ids = [];

		

		jQuery("#selected-products li").each(function(){
			product_ids.push(jQuery(this).attr('data-id'));
		});

		jQuery("#selected-products-2 li").each(function(){
			related_product_ids.push(jQuery(this).attr('data-id-2'));
		});


		if(product_ids.length==0 || related_product_ids.length==0)
		{
			alert("Please select minimum 1 product in both sections to make related");
			return false;
		}


		product_ids = product_ids.toString();
		related_product_ids = related_product_ids.toString();

		jQuery("#submit_loader").show();

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"product_ids":product_ids,"related_product_ids":related_product_ids,"action":"add_related_products"},
			type: "POST",
			success: function(result)
			{
        		var obj = jQuery.parseJSON(result);
    			if(obj.response==='TRUE')
    			{
	        			alert('Related products added successfully');
	        			jQuery("#submit_loader").hide();
    			}

    		}
    	});

	});

	


	/* ########################### manage related products ##############################*/
	jQuery("form#manage-related").on('change',"#manage_product_category",function(){
		var p_slug = jQuery(this).val();
		var this_elm = jQuery(this);

		if(p_slug=='')
		{
			jQuery("#manage_products").html("Please select category to load products");
			return false;
		}
		
		this_elm.next("img").show();

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"p_slug":p_slug,"action":"manage_load_products"},
			type: "POST",
			success: function(result)
			{
        		jQuery("#manage_products").html(result);
        		this_elm.next("img").hide();
        		jQuery('ul#fetch-related-products').empty();

        		jQuery("#manage-select-products").show();
    		}
    	});
	});




	jQuery("form#manage-related").on('click',".existing_related_product",function(){
        if(jQuery(this).is(':checked'))
        {
            var product_id = jQuery(this).attr('data-id');

            jQuery("#related_loader").show();

            jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"product_id":product_id,"action":"get_related_products"},
			type: "POST",
			success: function(result)
			{
        		jQuery("#fetch-related-products").html(result);
        		jQuery("#related_loader").hide();
    		},
    		complete: function(result)
    		{
    			// jQuery("#selected-products li").each(function(i,e){
    			// 	jQuery('#product-'+jQuery(this).attr('data-id')).attr('checked', true);
    			// });

            	jQuery("#manage-select-products-fetch").show();
    		}
    		});

        }
    });

	jQuery("form#manage-related").on('click',".remove_related",function(){
        if(confirm("Are you sure want to delete this from related products."))
        {
            var data_product_id = jQuery(this).parent("li").attr('data-product-id');
            var data_related_id = jQuery(this).parent("li").attr('data-related-id');
            var elm = jQuery(this).parent("li");

            jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"data_product_id":data_product_id,"data_related_id":data_related_id,"action":"remove_related_products"},
			type: "POST",
			success: function(result)
			{
        		var obj = jQuery.parseJSON(result);
    			if(obj.response==='TRUE')
    			{
	        			elm.remove();
    			}
    			else if(obj.response==='FALSE')
    			{
	        			alert('oops something went wrong!');
    			}

    		}
    		});

        }
    });





})