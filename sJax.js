			$('[href]').click(function(){
					$.ajax ({
						url: $(this).attr('href'),
						type: 'POST', data: { al: '1' },
						success: function(obj){ $('#content').html(obj); }, 
						error: function(request, status) {
							// exception
						}
				}); return false;
			});