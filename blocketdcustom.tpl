{if $etdhook|strlen}<!--[ETDHOOK:{$etdhook|strtoupper}]-->{/if}
<div class="mod-blocketdcustom{if $css|strlen} {$css}{/if}">
	<div class="etd-block">
		{if $showtitle}
		<div class="title">
			<h1>{$title}</h1>
		</div>
		{/if}
		<div class="content">
			{$content}
		</div>
	</div>
</div>
{if $etdhook|strlen}<!--[/ETDHOOK:{$etdhook|strtoupper}]-->{/if}

