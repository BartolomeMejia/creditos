<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style>
        <?php 	include( public_path() . '/css/fonts-roboto.css' );?>
    </style>
	<style type="text/css">
		html {
			margin: 0;
		}
		body {
			margin: 2mm 8mm 2mm 8mm;
		}
		.body-width {
			width: 50%;
		}
		div.top-bar {
			height: 2px;
		}
		div.blue-bar {
			height: 20px;
			background: #134794;
		}
		div.title-bar {
			height: 140px;
			background-size: cover;
			padding: 20px 60px 20px 40px;
		}
		div.title {
			font-family: 'Roboto', sans-serif;
			text-align: center;
			height: 18px;
			font-size: 15px;
			color: black;
		}
		table{
			width: 100%;
			font-family: 'Roboto', sans-serif;
			font-size: 11px;
		}
		span.note{
			font-size:12px;
			font-family: 'Roboto', sans-serif;
		}
		td.firstcolumninfo{
			width: 48%;
		}
		td.secundcolumninfo{
			width: 18%;
		}
		td.firstcolumnlabel{
			width: 16%;
		}
		td.secundcolumnlabel{
			width: 18%;
		}
		table.tablapago{
			width: 100%;
			border-collapse: collapse;
		}
		thead.pago{
			background: #134794;
		}
		td.columna{
			width: 25%;
			text-align: center;
			color: #ffffff;
		}
		tr.primeracolumna{
			width: 50%;
			height: 15px;
			border: 1px solid black;
		}
		td.columnapago{
			height: 15px;
			border: 1px solid black;
			font-size: 11px;
			text-align: center;	
		}
		td.rowfirm{
			height: 70px;
			border-bottom: 1px solid black;
		}
		td.fingerprint{
			height: 70px;
			border: 2px solid black;
		}
		td.rowfirmlabel{
			font-size: 12px;
			text-align: center;	
		}

	</style>

	<body class="body-width">	
	<div class="title">BOLETA DE CONTROL DE PAGO</div>
	<br>
		<table>
			<tr>
				<td class="firstcolumnlabel">Nombre Cliente:</td>
				<td class="firstcolumninfo">
					<span><strong>{!! $data->cliente->nombre.' '.$data->cliente->apellido !!}</strong></span>
				</td>
				<td class="secundcolumnlabel">DPI:</td>
				<td class="secundcolumninfo">
					<span><strong>{!! $data->cliente->dpi !!}</strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Dirección:</td>
				<td class="firstcolumninfo">
					<span><strong>{!! $data->cliente->direccion!!}</strong></span>
				</td>
				<td class="secundcolumnlabel">Teléfono:</td>
				<td class="secundcolumninfo">
					<span><strong>{!! $data->cliente->telefono!!}</strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Plan:</td>
				<td class="firstcolumninfo">
					<span><strong>{!! $data->planes->descripcion!!}</strong></span>
				</td>
				<td class="secundcolumnlabel">Fecha de entrega:</td>
				<td class="secundcolumninfo">
					<span><strong>{!! $data->fecha_inicio!!}</strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Monto:</td>
				<td class="firstcolumninfo">
					<span><strong>Q. {!!number_format((float)$data->montos->monto, 2, '.', '')!!}</strong></span>
				</td>
				<td class="secundcolumnlabel">Cuota diaria:</td>
				<td class="secundcolumninfo">
					<span><strong>Q. {!!number_format((float)$data->cuota_diaria, 2, '.', '')!!}</strong></span>
				</td>
			</tr>
		
		</table>
		<br>
		<?php 

			$totaldias = (strtotime($data->fecha_inicio)-strtotime($data->fecha_fin))/86400;
			$totaldias = abs($totaldias); 
			$totaldias = floor($totaldias + 1 );		

			$dias = intval(($totaldias / 2));
			$residuo = ($totaldias % 2);

			$dateInitial = new \DateTime($data->fecha_inicio);
			$secondsInitial = 0;
			$timestampInitial = $dateInitial->getTimestamp();
			$totalSunday = 0;

			for ($i=0; $i<$dias; $i++)  {  
				$secondsInitial = $secondsInitial + 86400;  
				$caduca = date("D", $timestampInitial+$secondsInitial);  
				
				if ($caduca == "Sun")  {  
					$i--;
					$totalSunday++;  
				}   
			}
			$residuoSunday = ($totalSunday % 2);
		?>
		<table class="tablapago">
			<thead class="pago">
				<tr>
					<td class="columna">No.</td>
					<td class="columna">Monto</td>
					<td class="columna">Fecha pago</td>
					<td class="columna">No.</td>
					<td class="columna">Monto</td>
					<td class="columna">Fecha pago</td>
				</tr>
			</thead>
			<tbody>
			<?php
				$date = new \DateTime($data->fecha_inicio);
				$segundos = 0;
				$timestamp = $date->getTimestamp();
				$cant1 = 0;
				$cant2 = 0;
				$count1 = 0;
				if($residuoSunday > 0)
					$count2 = $dias+$residuo-$totalSunday+1;
				else
					$count2 = $dias+$residuo-$totalSunday;
			?>
				@for ($i = 0; $i < $dias; $i++)
					<?php
				
						$caduca = date("D", $timestamp+$segundos);  

						if ($caduca == "Sun")  {  
							$i--;
						} else {
							$fecha1 = strtotime ( '+'.$cant1.' day' , strtotime ( $data->fecha_inicio) ) ;
							$fecha1 = date ( 'd-m-Y' , $fecha1 );
							$date1 = new \DateTime($fecha1);
							$sunday1 = date("D", $date1->getTimestamp());

							$fecha2 = strtotime ( '+'.$cant2.' day' , strtotime ( $data->fecha_inicio ) ) ;
							$fecha2 = date ( 'd-m-Y' , $fecha2 );
							$date2 = new \DateTime($fecha2);
							$sunday2 = date("D", $date2->getTimestamp());
					?>
							<tr class="primeracolumna">
								@if($sunday1 != "Sun")
									<?php $count1++; ?>
									<td class="columnapago">{!! $count1 !!}</td>
									<td class="columnapago">Q. {!!number_format((float)($data->deudatotal-($data->cuota_diaria * ($count1-1))), 2, '.', '')!!}</td>
									<td class="columnapago">{!! $fecha1 !!}</td>
								@else
									<td class="columnapago" colspan="3"><strong>DOMINGO</strong></td>									
								@endif
								@if($sunday2 != "Sun")
									<?php $count2++; ?>
									@if($count2 <= $data->planes->dias)
										<td class="columnapago">{!! $count2 !!}</td>
										<td class="columnapago">Q. {!!number_format((float)($data->deudatotal-($data->cuota_diaria * ($count2-1))), 2, '.', '')!!}</td>
										<td class="columnapago">{!! $fecha2 !!}</td>
									@else
										<td class="columnapago"></td>
										<td class="columnapago"></td>
										<td class="columnapago"></td>
								@endif
								@else
									<td class="columnapago" colspan="3"><strong>DOMINGO</strong></td>		
								@endif
							</tr>
						<?php }; 
						$segundos = $segundos + 86400;  
						
						$cant1 = $i+1;
						$cant2 = $i+1+$dias+($residuo);
						
						?>
				@endfor
				@if ($residuo>0)
					<?php 
						$cant3 = $dias+$residuo;
						if($residuoSunday > 0)
							$count3 = $dias+$residuo-$totalSunday+1;
						else
							$count3 = $dias+$residuo-$totalSunday;
					
						$fecha3 = strtotime ( '+'.$cant3.' day' , strtotime ( $data->fecha_inicio ) );
						$fecha3 = date ( 'j-m-Y' , $fecha3 );
					?>
					<tr class="primeracolumna">
						<td class="columnapago">{!! $count3 !!}</td>
						<td class="columnapago">Q. {!!number_format((float)($data->deudatotal-($data->cuota_diaria * ($count3-1))), 2, '.', '')!!}</td>
						<td class="columnapago">{!! $fecha3 !!}</td>
						<td class="columnapago" colspan="3"><strong>DOMINGO</strong></td>		
					</tr>
				@endif
			</tbody>
		</table>
		<span class="note"><strong>Nota: </strong>SE COBRARÁ MORA POR DÍA ATRASADO</span>
		<table>
			<tr>
				<td class="rowfirm"></td>
				<td class="rowfirm"></td>
				<td class="rowfirm"></td>
				<td class="fingerprint"></td>
			</tr>
			<tr>
				<td class="rowfirmlabel">F. Préstamos</td>
				<td class="rowfirmlabel">F. Encargada de grupo</td>
				<td class="rowfirmlabel">F. Cliente</td>
				<td class="rowfirmlabel">Huella</td>
			</tr>
		
		</table>

	</body>
