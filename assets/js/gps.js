var veh = {
	reportlink:site_url+'thong-ke',
	ini:function(){
		if( $('#vehicles-tables') ){
			this.vehiclesTable($('#vehicles-tables'));
		}
	},
	vehiclesTable:function(tb){
		$.each($('tbody tr',tb),function(i, item){
			 $('td:eq(6)',item).append( veh.mbutton('arrow-r',i18n.view_tracking) );
			 $('td:eq(6)',item).append( veh.mbutton('grid',i18n.view_report,'item-report') );
			 $('td:eq(6)',item).append( veh.mbutton('arrow-r',i18n.view_history) );
		});
		
		$('a.item-tracking').click(function(){
			 alert('edit item');
		 });
		 $('a.item-report').click(function(e){
			 e.preventDefault();
//			 id = $(this).parents('tr').attr('veh');
			// alert(id);
			 window.location.href =  veh.reportlink+'?veh='+$(this).parents('tr').attr('veh'); 
			 return false;
		 });
	},
	mbutton:function(icon,title,cln){
		title = (title)?title:'button title';
		cln = 	(cln)?cln:'actions';
		return $('<a/>',{
			'data-role' : 'button',
			'data-icon':icon,
			'data-iconpos':'notext',
			'data-theme':'c',
			'data-inline':'true',
			'title':title,
			'class':cln
			
		});
	},
};


var manager = {
	tableAction:function(){
		$('.item-edit').click(function(e){
			$form = $('form[name=update-vehicle]');
			$item =  $(this).nextAll('input[type=hidden]');
			if( $('input[name='+$item.attr("name")+']',$form) > 0 ){
				$('input[name='+$item.attr("name")+']',$form).val($item.val());
			} else {
				$form.append( $item.clone() );
			}
			$form.submit();
		});
		$('.item-tracking').click(function(e){
			window.location.href =  site_url+'theo-doi/'+$(this).nextAll('input[type=hidden]').val()+'.html';
		});
		$('.item-report').click(function(e){
			window.location.href =  site_url+'thong-ke/'+$(this).nextAll('input[type=hidden]').val()+'.html';
		});
		$('.item-history').click(function(e){
			window.location.href =  site_url+'lich-su/'+$(this).nextAll('input[type=hidden]').val()+'.html';
		});

		$('.item-shutdown').click(function(e){
			$form = $('form[name=update-vehicle]');
			$item =  $(this).nextAll('input[type=hidden]');
			if( $('input[name='+$item.attr("name")+']',$form) > 0 ){
				$('input[name='+$item.attr("name")+']',$form).val($item.val());
			} else {
				$form.append( $item.clone() );
			}
			$form.attr('action',site_url+'quan-ly/tat-thiet-bi.html').submit();
		});
		
		$('.item-turnon').click(function(e){
			$form = $('form[name=update-vehicle]');
			$item =  $(this).nextAll('input[type=hidden]');
			if( $('input[name='+$item.attr("name")+']',$form) > 0 ){
				$('input[name='+$item.attr("name")+']',$form).val($item.val());
			} else {
				$form.append( $item.clone() );
			}
//			alert('trun on'); return false;
			$form.attr('action',site_url+'quan-ly/mo-thiet-bi.html').submit();
		});
	},
};