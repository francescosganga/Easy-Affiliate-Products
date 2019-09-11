<div class="eap-product-single eap-column" style="width: {box_width}%">
	<div class="eap-product-inner">
		<div class="eap-product-image">
			<img src="{item_image}" />
		</div>
		<div class="eap-product-description">
			<div class="eap-product-title">{item_title}</div>
			<div class="eap-product-price">
				{item_price}
			</div>
		</div>
		<div class="eap-product-buy-button">
			<form method="GET" action="https://www.amazon.{amazon_country}/gp/aws/cart/add.html">
				<input type="hidden" name="AssociateTag" value="{amazon_tag}" />
				<input type="hidden" name="SubscriptionId" value="{amazon_api_access_key}" />
				<input type="hidden" name="ASIN.1" value="{item_asin}" /><br />
				<input type="hidden" name="Quantity.1" value="1" /><br/>
				<input type="submit" name="add" class="eap-product-btn" value="{buy_now_button}" />
			</form>
		</div>
	</div>
</div>