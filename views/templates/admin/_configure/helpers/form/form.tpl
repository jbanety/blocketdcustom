{**
 * @package     blocketdcustom
 *
 * @version     0.0.1
 * @copyright   Copyright (C) 2014 Jean-Baptiste Alleaume. Tous droits réservés.
 * @license     http://alleau.me/LICENSE
 * @author      Jean-Baptiste Alleaume http://alleau.me
 *}
{extends file="helpers/form/form.tpl"}

{block name="label"}
	{if $input.type == 'customs'}

	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name="legend"}
	<h3>
		{if isset($field.image)}<img src="{$field.image}" alt="{$field.title|escape:'html':'UTF-8'}" />{/if}
		{if isset($field.icon)}<i class="{$field.icon}"></i>{/if}
		{$field.title}
		<span class="panel-heading-action">
			{foreach from=$toolbar_btn item=btn key=k}
				{if $k != 'modules-list' && $k != 'back'}
					<a id="desc-{$table}-{if isset($btn.imgclass)}{$btn.imgclass}{else}{$k}{/if}" class="list-toolbar-btn" {if isset($btn.href)}href="{$btn.href}"{/if} {if isset($btn.target) && $btn.target}target="_blank"{/if}{if isset($btn.js) && $btn.js}onclick="{$btn.js}"{/if}>
						<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s=$btn.desc}" data-html="true">
							<i class="process-icon-{if isset($btn.imgclass)}{$btn.imgclass}{else}{$k}{/if} {if isset($btn.class)}{$btn.class}{/if}" ></i>
						</span>
					</a>
				{/if}
			{/foreach}
			</span>
	</h3>
{/block}

{block name="input"}

	{if $input.type == 'exceptions'}
		<input class="form-control" type="text" name="{$input.name}" size="40" value="{implode(',', $fields_value[$input.name])}" id="{$input.name}">
	{elseif $input.type == 'hooks'}
		{assign var=customfield value=$input.name|cat:'_custom'}
		<select class="form-control fixed-width-xl" name="{$input.name}" id="{$input.name}">
			<option value="">----------</option>
			<option value="custom">Personnalisé</option>
			<option value="">----------</option>
		{foreach $input.hooks as $hook}
			<option value="{$hook}"{if $hook == $fields_value[$input.name]} selected{/if}>{$hook}</option>
		{/foreach}
		</select>
		<input class="form-control" type="text" name="{$customfield}" value="{$fields_value[$customfield]}" id="{$customfield}">
		<script>
			jQuery(document).ready(function() {ldelim}
				$('#{$input.name}').on('change', function() {ldelim}
					var $this = $(this),
						$custom = $('#{$customfield}');
					if ($this.val() == "custom") {ldelim}
						$custom.show();
					{rdelim} else {ldelim}
						$custom.hide();
					{rdelim}
				{rdelim});
			{rdelim});
		</script>
	{else}
		{$smarty.block.parent}
    {/if}

{/block}

{block name="input_row"}

	{if $input.type == 'customs'}

		{assign var=customs value=$input.values}
		{if isset($customs) && count($customs) > 0}
			<div class="row">
				<div class="col-lg-9 col-lg-offset-3">
					<div class="panel">
						<div class="panel-heading">
							{$input.label}
						</div>
						<table class="table">
							<thead>
							<tr>
								<th>{l s='Name' mod='blocketdcustom'}</th>
								<th>{l s='Status' mod='blocketdcustom'}</th>
								<th>{l s='Access' mod='blocketdcustom'}</th>
								<th>{l s='Hook' mod='blocketdcustom'}</th>
								<th>&nbsp;</th>
							</tr>
							</thead>
							<tbody>
							{foreach $customs as $key => $custom}
								<tr {if $key%2}class="alt_row"{/if}>
									<td>
										{$custom.title|escape}
									</td>
									<td>
										<img src="{$smarty.const._PS_ADMIN_IMG_}{if $custom.published}enabled.gif{else}disabled.gif{/if}" alt="{if $custom.published}{l s='Enabled'}{else}{l s='Disabled'}{/if}" title="{if $custom.published}{l s='Enabled'}{else}{l s='Disabled'}{/if}" />
									</td>
									<td>
										{if $custom.access == 0}
											{l s='Public'}
										{elseif $custom.access == 1}
											{l s='Guests'}
										{elseif $custom.access == 2}
											{l s='Customers'}
										{/if}
									</td>
									<td>
										{$custom.hook}
									</td>
									<td>
										<div class="btn-group-action">
											<div class="btn-group pull-right">
												<a class="btn btn-default" href="{$current}&token={$token}&editCustom&id_custom={(int)$custom.id}" title="{l s='Edit' mod='blocketdcustom'}">
													<i class="icon-edit"></i> {l s='Edit' mod='blocketdcustom'}
												</a>
												<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
													<i class="icon-caret-down"></i>&nbsp;
												</button>
												<ul class="dropdown-menu">
													<li>
														<a href="{$current}&token={$token}&deleteCustom&id_custom={(int)$custom.id}" title="{l s='Delete' mod='blocketdcustom'}">
															<i class="icon-trash"></i> {l s='Delete' mod='blocketdcustom'}
														</a>
													</li>
												</ul>
											</div>
										</div>
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		{else}
			<p>{l s='No custom blocks have been created.' mod='blocketdcustom'}</p>
		{/if}

	{else}
		{$smarty.block.parent}
	{/if}

{/block}
