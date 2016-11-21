var repo = {
	chart:'',
	ajaxLink:site_url+'thong-ke/bieu-do',
	report_data:[],day :'',month : '',year :'',
	chartyAxis:{},
	action:function(){
		
		
	},
	load:function($time){
		this.day =  $('select[name=rday]');
		this.month = $('input[name=rmonth]');
		this.year =  $('input[name=ryear]');
		
		$("#gps-vehicles").change(function() {
			 window.location.href = site_url+'thong-ke/'+$(this).val()+'.html';  
		});
		
		
		var $timeTaget = new Date();
		if($time){
			$timeTaget = new Date(vmap.strtotime($time)*1000);
			
		}
		this.day.val( $timeTaget.getDate() );
		this.month.val( $timeTaget.getMonth()+1);
		this.year.val( $timeTaget.getFullYear());
		
		$monthCurrent = 0;
		$('input[name=time-next]').click(function(e){
			$monthCurrent = parseInt(repo.month.val());
			if($monthCurrent < 12 ) repo.month.val($monthCurrent+1);
			else {
				repo.month.val(1);
				repo.year.val(parseInt(repo.year.val())+1);
			}
			repo.day.val(0);
			e.preventDefault();
			repo.getData();
		});
		$('input[name=time-pre]').click(function(e){
			$monthCurrent = parseInt(repo.month.val());
			if($monthCurrent > 1 ) repo.month.val($monthCurrent-1);
			else {
				repo.month.val(12);
				repo.year.val(parseInt(repo.year.val())-1);
			}
			repo.day.val(0);
			e.preventDefault();
			repo.getData();
		});
		this.day.change(function(e){
				repo.day.val( $(this).val() );
				e.preventDefault();
				repo.getData();
		});
		repo.getData();
	},
	ini:function(Chartcategory,Chartdata,chartTitle,$yAxis){
			
		var chart = new Highcharts.Chart({
            chart: { renderTo: 'chart', type: 'column' },
            title: { text: 'Thống Kê Đường Đi Theo Thời Gian' },
            subtitle: { text: chartTitle },
            xAxis: {
            	categories :Chartcategory
            },
            yAxis:$yAxis,
            tooltip: {
                formatter: function() {
                	$tip = Math.round(this.y*100)/100;
                	if(this.series.name =='Tiền Xăng')
                		$tip+=' VNĐ';
                	else if (this.series.name =='Xăng')
                		$tip+=' lít';
                	else
                		$tip+=' Km';
                    return $tip;
                }
            },
           series: Chartdata
        });
	},
	getData:function(){
		$('#chart').html('');
		
		$.ajax({
			  url: site_url+'thong-ke/'+$('input[name=vid]').val()+'/bieu-do.json',dataType: 'json',
//			  data: {'vehicle':$('input[name=vehicle]').val(),'day':$('select[name=rday]').val(),'month':repo.month.val(),'year':$('input[name=ryear]').val()},
			  data: {'day':$('select[name=rday]').val(),'month':repo.month.val(),'year':$('input[name=ryear]').val()},
			  beforeSend: function(){ $('.ajax-modal').show(); },
			  success: function(val){
				  if(val){
					  var cate = [];
					  var dataVal = []; var $fuel = []; var $money = [];
					  $length = 0;
					  $.each(val.node, function(i, item) {
						    if(val.type=='year'){
								  cate.push('Tháng '+i);
							  } else if (val.type=='month'){
								  cate.push((i));
							  } else if(val.type=='day'){
								  cate.push(i+' Giờ');
							  }
							  dataVal.push(item);
							  //alert(item);
							  if(val.fuel){
								  $fuel.push(val.fuel[i]);
								  //alert(val.fuel[i]);
							  }
							  if(val.money){
								  $money.push(val.money[i]);  
							  }
							  $length++;
						});
					  //for (i in val.node) {
					  //}
					if(val.type=='year'){
						title = 'Dữ Liệu Năm '+$('input[name=ryear]').val();
					} else if (val.type=='month'){
						title = 'Dữ Liệu Tháng '+$('input[name=rmonth]').val()+'-'+$('input[name=ryear]').val();
					} else if(val.type=='day'){
						title = 'Dữ Liệu Ngày '+$('select[name=rday]').val()+'-'+$('input[name=rmonth]').val()+'-'+$('input[name=ryear]').val();
					}
					$charData = [{
						name: 'Quãng Đường', color: '#4572A7', type: 'column', yAxis: 0, data : dataVal,
						
		            }];
					$chartyAxis = [{ 
						title: {  text: 'Quãng đường đã đi (Km)', style: { color: '#4572A7' } }, 
						labels: { formatter: function() { return this.value +'Km'; },style: { color: '#4572A7' } },
						startOnTick: false,
			            showFirstLabel: false,
			            type: 'linear'
					}];
					if($fuel.length > 0 ){
						$charData.push({name:'Xăng', color: '#AA4643',yAxis: 1,  marker: { enabled: false }, dashStyle: 'shortdot', type: 'spline',data:$fuel});
						$chartyAxis.push({ title: { text: 'Lượng Xăng Sử Dụng', style: { color: '#AA4643' } }, labels: {  formatter: function() { return this.value +' Lit'; }, style: { color: '#AA4643' } }, opposite: true, min: 0 });
					}
					if($money.length > 0 ){
						$charData.push({name:'Tiền Xăng', color: '#89A54E', type: 'spline',data:$money, yAxis: 2});
						$chartyAxis.push({ title: { text: 'Tiền Xăng', style: { color: '#89A54E' } }, labels: {  formatter: function() { return this.value +' VND'; }, style: { color: '#89A54E' } }, opposite: true });
					}
					$('span[name=length_road]').html(val.length_road);
					$('span[name=max_speed]').html(val.max_speed.max);
					if(val.max_speed.max !='0' && val.max_speed.time){
						$('span[name=time_max]').html(val.max_speed.time).parent().show();
					} else {
						$('span[name=time_max]').parent().hide();
					}
					$('span[name=moving_time]').html(val.moving_time);
					
					if(val.fuel){
						//$fuel = val.fuel;
						//$fuelTotal = parseFloat( val.fuel[Object.keys(val.fuel).length - 1] );
						$fuelTotal = parseFloat( val.fuel[ $length - 1] );
						//alert(val.fuel[20]);
						//alert($fuel[3]);
						//alert(val.fuel[ $length - 1]);
						
						$('span[name=fuel]').html($fuelTotal+' Lít');
						if(val.fuel_price){
							$('span[name=fuel_price]').html( parseFloat( $fuelTotal*parseFloat( val.fuel_price) ).toFixed(0) +' VNĐ' );
						} else {
							$('span[name=fuel_price]').html('0 VNĐ');
						}
						
					}else {
						$('span[name=fuel]').html('0 Lít');
						$('span[name=fuel_price]').html('0 VNĐ');
					}
					
					
					
					//$('span[name=max_speed]').html(val.max_speed);
					repo.ini(cate,$charData,title,$chartyAxis);
					$('span[name=statistic_title]').html(title);
				  }
				  
			  },
			  complete :function(){
				  stoptable.load();
				  $('.ajax-modal').hide();
			  }
			  //
		});
	},
};

var stoptable = {
	config : {
        "bProcessing": true,"bServerSide": true, "sPaginationType": "full_numbers", "bFilter": false,"sDom": '<"top"i>rt<"bottom"flp><"clear">',
        "fnServerData": function ( sSource, aoData, fnCallback ) {
			jQuery.ajax( {"dataType": 'json',"type": "GET","url": site_url+'thong-ke/'+$('input[name=vid]').val()+'/diem-dung.json',"data": aoData,
	            success: function(data) { 
	            	fnCallback(data); 
	            	$('.node_stop').css({'cursor':'pointer'}).click(function(){
	            		vmap.popup( $(this) );
	            	});
	            } 
            });
    	},
    	"oLanguage": {
			"sLengthMenu": "Lựa Chọn Hiển Thị _MENU_ Điểm Dừng Trên Một Trang",
			"sZeroRecords": "Không Có Điểm Dừng Nào",
			"sInfo": "Hiển Thị _START_ đến _END_ trong tổng số _TOTAL_ Điểm Dừng",
			"sInfoEmpty": "Hiển Thị 0 đến 0 của 0 Điểm Dừng",
			"sInfoFiltered": "(filtered from _MAX_ total records)"
		}
    	
	},
	load:function(){
		stoptable.config['fnServerParams']=function (aoData) {
			aoData.push( { "name": vmap.token.name, "value": vmap.token.val } );
			aoData.push( { "name": 'year', "value": $('input[name=ryear]').val() } );
			aoData.push( { "name": 'month', "value": $('input[name=rmonth]').val() } );
			aoData.push( { "name": 'day', "value": $('select[name=rday]').val() } );
			aoData.push( { "name": 'vehicle', "value": $('input[name=vid]').val() } );
		};
		jQuery('#node-data').dataTable().fnDestroy();
		var oTable = $('#node-data').dataTable(stoptable.config);
	}
};