<div id="wpmm-item-details-header">
    <p id="wpmm-item-details-name"><?php echo $options['name']; ?></p>
</div>
<div id="wpmm-item-details-content">
    <div id="wpmm-item-details-content-right" class="noimage" style="padding-top: 0px; padding-bottom: 0px; line-height: 15px;">
        <table border="0">
            <?php if (!empty($options['street'])) { ?>
                <tr><td class="wpmm-color-text"><b>Address: </b></td><td><?php
            echo $options['street']; $address.=$options['street'];
            if (!empty($options['city'])){
                echo ', ' . $options['city'];$address.=', ' . $options['city'];}
            if (!empty($options['state'])){
                echo ', ' . $options['state'];$address.=', ' . $options['state'];}
            if (!empty($options['zip']))
                echo ' ' . $options['zip'];
            echo '.';
                ?></td></tr> <?php
                }
            ?>
            <?php if (!empty($options['phone'])) { ?>
                <tr><td class="wpmm-color-text"><b>Phone: </b></td><td><?php
            echo $options['phone'];
                ?></td></tr> <?php
                }
            ?>
            <?php if (!empty($options['fax'])) { ?>
                <tr><td class="wpmm-color-text"><b>Fax: </b></td><td><?php
            echo $options['fax'];
            ?></td></tr> <?php
        }
            ?>
            <?php if (!empty($options['email'])) { ?>
                <tr><td class="wpmm-color-text"><b>E-mail: </b></td><td><?php
            echo $options['email'];
                ?></td></tr> <?php
        }
            ?>
        </table>
    </div>
</div>
<div id="wpmm-item-details-more">
    <div style="padding-left:8px;padding-right:8px;">
<?php if (!empty($options['delivery_charge'])) { ?>
            <strong class="wpmm-color-text">Delivery Charge: </strong><?php
    echo '$' . $options['delivery_charge'];
    ?><br> <?php
    }
?>
        <?php if (!empty($options['min_order'])) { ?>
            <strong class="wpmm-color-text">Minimum order amount: </strong><?php
        echo '$' . $options['min_order'];
            ?><br> <?php
    }
        ?>
        <script>
            jQuery(document).ready(function(){
                var mapOptions = {
                    center: new google.maps.LatLng(-34.397, 150.644),
                    zoom: 8,
                    disableDefaultUI: true,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                var map = new google.maps.Map(document.getElementById("wpmm-google-map"),
                mapOptions);
                map.setOptions({draggable: false, zoomControl: false, scrollwheel: false, disableDoubleClickZoom: true});
                var address='<?php echo $options['zip']; ?>';
                var shopaddress='<?php echo $address; ?>';
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({ 'address': shopaddress }, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        var latitude = results[0].geometry.location.lat();
                        var longitude = results[0].geometry.location.lng();
                        var marker = new google.maps.Marker({
                            position: new google.maps.LatLng(latitude,longitude),
                            map: map,
                            title:"<?php echo $options['name']; ?>"
                        });
                    }
                });
                geocoder.geocode({ 'address': address }, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        displayBounds(results[0].geometry.bounds);
                        map.setOptions({center : new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng())});
                    }
                });

            function displayBounds(bounds) {

                var rectangleOptions = {
                    strokeColor: '#ff0000',
                    strokeOpacity: 0.5,
                    strokeWeight: 3,
                    bounds: bounds
                }
                var rectangle = new google.maps.Rectangle(rectangleOptions);

                rectangle.setMap(map);

                map.fitBounds(bounds);
            }
            google.maps.event.trigger(map, 'resize');
            });
        </script>
        <div id="wpmm-google-map" style="width: 100%; height: 200px;">
    </div>
</div>
<div id="wpmm-item-details-footer">
    <a href="#" id="wpmm-item-details-close">Close</a>
</div>
<script>
    jQuery('#wpmm-item-details').draggable({ handle: "#wpmm-item-details-header" });
    jQuery('#wpmm-item-details-close').on('click', function() {
        jQuery('#wpmm-item-details').hide();
        return false;
    });
</script>
