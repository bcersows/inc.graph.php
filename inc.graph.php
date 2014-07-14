<?php
	function graph_bars($data = array()){
		if(!isset($data['graph.height'])){$data['graph.height'] = 140;}
		if(!isset($data['cell.width'])){$data['cell.width'] = 30;}
		if(!isset($data['cell.marginx'])){$data['cell.marginx'] = 0;}
		if(!isset($data['cell.marginy'])){$data['cell.marginy'] = 2;}
		if(isset($data['header'])){$data['cell.marginy'] += isset($data['header.height']) ? $data['header.height'] : 22;}
		if(!isset($data['graph.legend.width'])){$data['graph.legend.width'] = 30;}

		if(!isset($data['bar.color'])){$data['bar.color'] = 'red';}
		$data['bar.indicator'] = (isset($data['bar.indicator'])) ? 16 : 0;
		$data['cell.width.half'] = $data['cell.width']/2;
		$data['items.count'] = key($data['graph']);$data['items.count'] = count($data['graph'][$data['items.count']]);
		$data['graph.width'] = ($data['items.count']*$data['cell.width'])+$data['items.count']+1;

		if(!isset($data['graph.min'])){
			$data['graph.min'] = false;
			foreach($data['graph'] as $name=>&$row){foreach($row as $k=>&$v){if($data['graph.min'] === false || $v < $data['graph.min']){$data['graph.min'] = $v;}}}
		}
		if(!isset($data['graph.max'])){
			$data['graph.max'] = false;
			foreach($data['graph'] as $name=>&$row){foreach($row as $k=>&$v){if($data['graph.max'] === false || $v > $data['graph.max']){$data['graph.max'] = $v;}}}
		}

		$data['graph.incr'] = ($data['graph.max']) ? ($data['graph.height']-2-($data['cell.marginy']*2)-$data['bar.indicator'])/($data['graph.max']-$data['graph.min']) : 0;
		$getHeight = function($h) use (&$data){return ($h-$data['graph.min'])*$data['graph.incr'];};
		$getTop = function($h) use (&$data){return $data['graph.height']-(($h-$data['graph.min'])*$data['graph.incr'])-1-$data['cell.marginy']-$data['bar.indicator'];};

		$svg = '<svg width="'.($data['graph.width']+$data['graph.legend.width']).'" height="'.$data['graph.height'].'">'.PHP_EOL.
		'<rect width="100%" height="100%" style="fill:#aaa;" />'.PHP_EOL;

		$svg .= '<g class="graph">'.PHP_EOL;
		$left = 1+$data['graph.legend.width'];for($i = 0;$i < $data['items.count'];$i++,$left += $data['cell.width']+1){
			$svg .= '<rect width="'.$data['cell.width'].'" height="'.($data['graph.height']-2).'" x="'.$left.'" y="1" style="fill:#fff;" />'.PHP_EOL;
		}
		if($data['graph.legend.width']){
			$svg .= graph_fragment_legend($data);
		}
		foreach($data['graph'] as $name=>&$row){
			$left = 1+$data['cell.marginx']+$data['graph.legend.width'];
			foreach($row as $k=>&$v){
				if(!is_array($v) && ($v = floatval($v)) ){
					$h = $getHeight($v);
					$t = $getTop($v);
					$svg .= '<rect width="'.($data['cell.width']-($data['cell.marginx']*2)).'" height="'.$h.'" x="'.$left.'" y="'.$t.'" style="fill:'.$data['bar.color'].';" rx="2" ry="2"/>'.PHP_EOL;
					if($data['bar.indicator']){
						$m = 4;
						$svg .= '<path d="M'.($left+$m).' '.($t+$h).' l'.($data['cell.width.half']-$data['cell.marginx']-$m).' 6 l'.($data['cell.width.half']-$data['cell.marginx']-$m).' -6 Z" style="fill:'.$data['bar.color'].';" />'.PHP_EOL;
						$svg .= '<text x="'.($left+$data['cell.width.half']-2).'" y="'.($t+$h+16).'" text-anchor="middle" style="fill:#444;font-size:10px;">'.round($v,2).'</text>'.PHP_EOL;
					}
				}
				$left += $data['cell.width']+1;
			}
		}
		$svg .= '</g>'.PHP_EOL;

		$svg .= graph_fragment_header($data);
		$data['header.top'] = ($data['graph.height']-$data['cell.marginy']+2);
		$svg .= graph_fragment_header($data);
		$svg .= '</svg>';

		return $svg;
	}
	function graph_fragment_legend(&$data = array()){
		if(!isset($data['graph.legend.count'])){
			//FIXME: realmente se puede basar en $data['graph.height']
			$data['graph.legend.count'] = 2;
		}
		$lineColor = '#ccc';
		$incr = ($data['graph.max']-$data['graph.min'])/($data['graph.legend.count']+1);
		$steps = array();$top = $data['cell.marginy']+1;$i = $data['graph.legend.count']+1;while(--$i){$steps[] = $i*$incr;}
		$height = ($data['graph.height']-($data['cell.marginy']*2)-$data['bar.indicator']-2);
		$incr = $height/($data['graph.max']-$data['graph.min']);

		$svg = '<g class="legend">'.PHP_EOL;
		$svg .= '<rect x="1" y="1" width="'.($data['graph.legend.width']-1).'" height="'.($data['graph.height']-2).'" style="fill:white;" />';

		$svg .= '<rect x="'.($data['graph.legend.width']).'" y="'.($data['cell.marginy']+1).'" width="'.($data['graph.width']).'" height="1" style="fill:'.$lineColor.';" />';
		$svg .= '<text x="'.($data['graph.legend.width']-2).'" y="'.($data['cell.marginy']+5).'" text-anchor="end" style="fill:#444;font-size:10px;">'.round($data['graph.max'],2).'</text>';

		foreach($steps as $step){
			$t = floor($height+$data['cell.marginy']-($step*$incr));
			$svg .= '<rect x="'.($data['graph.legend.width']).'" y="'.($t).'" width="'.($data['graph.width']).'" height="1" style="fill:'.$lineColor.';" />';
			$svg .= '<text x="'.($data['graph.legend.width']-2).'" y="'.($t+4).'" text-anchor="end" style="fill:#444;font-size:10px;">'.round($step,2).'</text>';
		}

		$svg .= '<rect x="'.($data['graph.legend.width']).'" y="'.($data['graph.height']-$data['cell.marginy']-$data['bar.indicator']-2).'" width="'.($data['graph.width']).'" height="1" style="fill:'.$lineColor.';" />';
		$svg .= '<text x="'.($data['graph.legend.width']-2).'" y="'.($data['graph.height']-$data['cell.marginy']-$data['bar.indicator']-2+4).'" text-anchor="end" style="fill:#444;font-size:10px;">'.round($data['graph.min'],2).'</text>';

		$svg .= '</g>'.PHP_EOL;
		return $svg;
	}
	function graph_fragment_header(&$data = array()){
		if(!isset($data['cell.width'])){$data['cell.width'] = 30;}
		if(!isset($data['header.height'])){$data['header.height'] = 22;}
		if(!isset($data['header.top'])){$data['header.top'] = 0;}
		if(!isset($data['graph.legend.width'])){$data['graph.legend.width'] = 0;}

		$svg = '<g class="header">'.PHP_EOL;
		$svg .= '<rect x="'.$data['graph.legend.width'].'" y="'.$data['header.top'].'" width="'.($data['graph.width']).'" height="'.$data['header.height'].'" style="fill:#aaa;" />';
		$left = 1+$data['graph.legend.width'];foreach($data['header'] as $label){
			$svg .= '<rect width="'.$data['cell.width'].'" height="'.($data['header.height']-2).'" x="'.$left.'" y="'.(1+$data['header.top']).'" style="fill:#fff;" />'.PHP_EOL;
			$svg .= '<text x="'.($left+$data['cell.width.half']).'" y="'.($data['header.height']/2+(10/2/* font-size */)-2+$data['header.top']).'" text-anchor="middle" style="fill:#444;font-size:10px;">'.$label.'</text>'.PHP_EOL;
			$left += $data['cell.width']+1;
		}
		$svg .= '</g>'.PHP_EOL;
		return $svg;
	}

