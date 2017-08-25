{if $etdhook|strlen}<!--[ETDHOOK:{$etdhook|strtoupper}]-->{/if}
{if $block_tag|strlen}<{$block_tag|escape:'htmlall':'UTF-8'}{if $block_class|strlen} class="{$block_class|escape:'htmlall':'UTF-8'}"{/if}>{/if}
	{if $showtitle}
		{if $title_tag|strlen}<{$title_tag|escape:'htmlall':'UTF-8'}{if $title_class|strlen} class="{$title_class|escape:'htmlall':'UTF-8'}"{/if}>{/if}
			{$title|htmlspecialchars:$smarty.const.ENT_QUOTES:'UTF-8'}
		{if $title_tag|strlen}</{$title_tag|escape:'htmlall':'UTF-8'}>{/if}
	{/if}
	{if $content_tag|strlen}<{$content_tag|escape:'htmlall':'UTF-8'}{if $content_class|strlen} class="{$content_class|escape:'htmlall':'UTF-8'}"{/if}>{/if}
		{$content}
	{if $content_tag|strlen}</{$content_tag|escape:'htmlall':'UTF-8'}>{/if}
{if $block_tag|strlen}</{$block_tag|escape:'htmlall':'UTF-8'}>{/if}
{if $etdhook|strlen}<!--[/ETDHOOK:{$etdhook|strtoupper}]-->{/if}