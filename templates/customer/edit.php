<div id="m-customer-detail">
    <div class="row">
        <div class="col-xl-3 col-lg-4">
            <div class="m-portlet m-portlet--full-height  ">
                <div class="m-portlet__body">
                    <div class="m-card-profile">
                        <div class="m-card-profile__pic">
                            <div class="m-card-profile__pic-wrapper">
                                <img src="<?php echo get_avatar_url($user->ID); ?>" alt="<?php echo $user->display_name; ?>"/>
                            </div>
                        </div>
                        <div class="m-card-profile__details">
                            <span class="m-card-profile__name"><?php echo $user->display_name; ?></span>
                            <a href="" class="m-card-profile__email m-link"><?php echo $user->user_email; ?></a>
                        </div>
                    </div>

                    <?php if (count($user_links) > 0) : ?>
                        <ul class="m-nav m-nav--hover-bg m-portlet-fit--sides">
                            <li class="m-nav__separator m-nav__separator--fit"></li>
                            <?php foreach ($user_links as $link) : ?>
                                <li class="m-nav__item">
                                    <a href="<?php echo $link['url']; ?>" class="m-nav__link"
                                       target="<?php echo $link['target']; ?>">
                                        <i class="m-nav__link-icon <?php echo $link['icon']; ?>"></i>
                                        <span class="m-nav__link-text"><?php echo $link['title']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (count($user_statistics) > 0) : ?>
                        <div class="m-portlet__body-separator"></div>
                        <div class="m-widget1 m-widget1--paddingless">
                            <?php foreach ($user_statistics as $statistic) : ?>
                                <div class="m-widget1__item">
                                    <div class="row m-row--no-padding align-items-center">
                                        <div class="col">
                                            <h3 class="m-widget1__title"><?php echo $statistic['title']; ?></h3>
                                        </div>
                                        <div class="col m--align-right">
                                            <span class="m-widget1__number <?php echo $statistic['number_class']; ?>"><?php echo $statistic['number']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-9 col-lg-8">
            <div class="m-portlet m-portlet--full-height m-portlet--tabs">
                <?php if (count($user_tabs) > 0) : ?>
                    <div class="m-portlet__head">
                        <div class="m-portlet__head-tools">
                            <ul class="nav nav-tabs m-tabs m-tabs-line m-tabs-line--left m-tabs-line--primary"
                                role="tablist">
                                <?php $i = 0;
                                foreach ($user_tabs as $tab_id => $tab) : ?>
                                    <li class="nav-item m-tabs__item">
                                        <a class="nav-link m-tabs__link <?php if ($i == 0) echo 'active'; ?>"
                                           data-toggle="tab"
                                           href="#<?php echo $tab_id; ?>"
                                           role="tab">
                                            <i class="flaticon-share m--hide"></i>
                                            <?php echo $tab['title']; ?>
                                        </a>
                                    </li>
                                    <?php $i++; endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="tab-content">
                        <?php $i = 0;
                        foreach ($user_tabs as $tab_id => $tab) : ?>
                            <div class="tab-pane <?php if ($i == 0) echo 'active'; ?>" id="<?php echo $tab_id; ?>">
                                <?php
                                if (method_exists($tab['callback'][0], $tab['callback'][1])
                                && is_callable($tab['callback']))
                                {
                                    call_user_func($tab['callback']);
                                } ?>
                            </div>
                        <?php $i++; endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>