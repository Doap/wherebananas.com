function setupYfAdmin()
{
	var $ = jQuery;
	$(function(){
		$('.yfSettingsForm').each(function(){
			var $ft = $(this).find('.feedtype');
			if($ft.length > 0 && $ft.data('events') && $ft.data('events').change && $ft.data('events').change.length > 0)
			{
				return;
			}
			var $t = $(this),$ftid=$t.find('.feedtypeid-id'),$ftstd=$t.find('.feedtypeid-standard'),
				$fis=$t.find('.feedidstandard'),$sfr=$t.find('.standardfeedregion'),$fid=$t.find('.feedid'),
				$ftun=$t.find('.feedtypeiddesc-username'),$ftpl=$t.find('.feedtypeiddesc-playlist'),
				$ftc=$t.find('.feedtypeiddesc-category'),$fts=$t.find('.feedtypeiddesc-search'),
				$ftidd=$t.find('.feedtypeiddesc'),$obp=$t.find('.orderby-p'),$obnp=$t.find('.orderby-np'),feedtype='',
				$po=$t.find('option[value="published"]'),$pd=$('.previewDiv');
			$ft.change(function(){
				feedtype = $(this).children(':selected').val();
				if(feedtype == 'standard')
				{
					$ftid.hide();
					$ftstd.show();
					$fid.prop('disabled', true);
					$fis.prop('disabled', false);
					$sfr.prop('disabled', false);
				}
				else
				{
					$ftid.show();
					$ftstd.hide();
					$fid.prop('disabled', false);
					$fis.prop('disabled', true);
					$sfr.prop('disabled', true);
				}
				$obp.prop('disabled', feedtype != 'playlist');
				$obnp.prop('disabled', feedtype == 'playlist');
				if(feedtype == 'playlist')
				{
					if($obnp.is(':selected'))
					{
						$po.prop('selected', true);
					}
				}
				else if($obp.is(':selected'))
				{
					$po.prop('selected', true);
				}
				$ftidd.hide();
				switch(feedtype)
				{
					case 'uploads':
					case 'favorites':
					case 'subscriptions':
						$ftun.show();
						break;
					case 'playlist':
						$ftpl.show();
						break;
					case 'category':
						$ftc.show();
						break;
					case 'search':
						$fts.show();
						break;
					default:
						break;
				}
			}).change();
			$t.find('.previewButton').click(function(e){
				var c = $(this).attr('class').split(' '), $cpd = $t.find('.' + c[1] + 'Div'), vis = $cpd.is(':visible');
				$pd.hide();
				if(!vis)
				{
					$cpd.show();
				}
				e.stopPropagation();
				return false;
			});
		});
	});
}
