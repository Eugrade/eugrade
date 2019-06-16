<?php require 'pro_header.php'; ?>




<div class="main-container">

    <div class="left">
        <a-spin :spinning="spinning.left">
            <div class="main-header">
                <h3>All Classes</h3>
                <p>All the classes you joined</p>
            </div>
            <template v-if="!!user.joined_classes">
                <div v-for="(joined,index) in user.joined_classes" :class="'class-item ' + class_super(index)" @click="open_class_info(index)" :id="'class'+index">
                    <div style="margin-right: 10px;">
                        <template v-if="!!user.classes_info[index].img">
                            <img :src="user.classes_info[index].img" class="class-item-img" />
                        </template>
                        <template v-else>
                            <div class="class-img-default">
                                <p>{{ user.classes_info[index].name.substring(0,1) }}</p>
                            </div>
                        </template>
                    </div>
                    <div>
                        <h3 v-html="user.classes_info[index].name+'<em>'+user.classes_info[index].member.split(',').length+'</em>'"></h3>
                        <p v-html="user.classes_info[index].des"></p>
                    </div>
                </div>
            </template>
            <div class="class-item" @click="add_class()">
                <?php if ($type == 1) { ?>
                    <p>
                        <a-icon type="plus-square"></a-icon>&nbsp;&nbsp;Join a new Class
                    </p>
                <?php } else { ?>
                    <p>
                        <a-icon type="plus-square"></a-icon>&nbsp;&nbsp;Create a new Class
                    </p>
                <?php } ?>
            </div>
        </a-spin>
    </div>

    <?php if ((int)$type == 2) { ?>
        <!-- 新建班级 -->
        <a-modal title="Create a new Class" :visible="add.visible" @ok="handle_create_submit" :confirm-loading="add.confirm_create_loading" @cancel="handle_create_cancel">
            <a-input placeholder="Class Name" v-model="add.class.name">
                <a-icon slot="prefix" type="team" />
            </a-input>
            <br /><br />
            <a-textarea placeholder="Class Description" v-model="add.class.des" :rows="4" />
        </a-modal>
        <!-- 新建班级结束 -->
    <?php } else { ?>
        <!-- 加入班级 -->
        <a-modal title="Join a new Class" :visible="add.visible" @ok="handle_join_submit" :confirm-loading="add.confirm_join_loading" @cancel="handle_create_cancel">
            <a-input placeholder="Class ID" v-model="add.join.id">
                <a-icon slot="prefix" type="team" />
            </a-input>
        </a-modal>
        <!-- 加入班级结束 -->
    <?php } ?>

    <div class="center class-center">
        <a-spin :spinning="spinning.center">
            <template v-if="opened_class_info.status">
                <div class="class-info-header">
                    <div style="margin-right: 12px;">
                        <template v-if="!!opened_class_info.img">
                            <img :src="opened_class_info.img" class="class-item-img class-info-img" />
                        </template>
                        <template v-else>
                            <div class="class-img-default class-info-img">
                                <p>{{ opened_class_info.name.substring(0,1) }}</p>
                            </div>
                        </template>
                    </div>
                    <div class="class-info-info">
                        <h2 v-html="opened_class_info.name"></h2>
                        <p>{{ opened_class_info.members.length }} Members</p>
                    </div>
                    <div class="class-settings">
                        <a-dropdown placement="bottomRight">
                            <a-button>Settings</a-button>
                            <a-menu slot="overlay">
                                <template v-if="user.id == opened_class_info.superid">
                                    <a-menu-item>
                                        <a>Edit Info</a>
                                    </a-menu-item>
                                </template>
                                <template v-else>
                                    <a-menu-item>
                                        <a @click="stu_remove(opened_class_info.id)">Leave the class</a>
                                    </a-menu-item>
                                </template>
                            </a-menu>
                        </a-dropdown>
                    </div>
                </div>
                <div class="class-info-des">
                    <p v-html="opened_class_info.des"></p>
                </div>
                <div class="class-info-admin">
                    <p>{{ opened_class_info.supername }} is the admin</p>
                </div>
                <div>
                    <div v-for="(member,index_info) in opened_class_info.members" class="class-item class-info-member" @click="open_member_info(member.id)" :id="'member'+member.id">
                        <div style="margin-right: 15px;">
                            <template v-if="!!member.avatar">
                                <img :src="member.avatar" class="class-item-img" />
                            </template>
                            <template v-else>
                                <div class="class-img-default">
                                    <p>{{ member.name.substring(0,1) }}</p>
                                </div>
                            </template>
                        </div>
                        <div style="width:100%">
                            <h3 v-html="member.name"></h3>
                            <p v-html="get_level(member.type)"></p>
                        </div>
                        <div class="class-info-member-icon">
                            <a-icon type="right-circle" />
                        </div>
                    </div>
                </div>
            </template>
        </a-spin>
    </div>

    <div class="right">
        <a-spin :spinning="spinning.right">
            <template v-if="opened_member_info.status">
                <div class="class-info-header class-member-header">
                    <div style="margin-right: 20px;">
                        <template v-if="!!opened_member_info.info.avatar">
                            <img :src="opened_member_info.info.avatar" class="class-item-img class-member-img" />
                        </template>
                        <template v-else>
                            <div class="class-img-default class-member-img">
                                <p>{{ opened_member_info.info.name.substring(0,1) }}</p>
                            </div>
                        </template>
                    </div>
                    <div class="class-info-info class-member-info" style="padding-top: 5px;">
                        <h2 v-html="opened_member_info.info.name"></h2>
                        <p>{{ get_level(opened_member_info.info.type) }} Account</p>
                    </div>
                    <div class="class-member-subscribe">
                        <!-- 只允许 opened_member 对应的 super 删除账户,super 不可删除自己 -->
                        <template v-if="(parseInt(opened_member_info.superid) == parseInt(user.id)) && (parseInt(user.id) !== parseInt(opened_member_info.info.id))">
                            <a-button type="danger" @click="tea_remove(opened_member_info.classid,opened_member_info.info.id)">
                                <a-icon type="delete"></a-icon>
                            </a-button>
                        </template>
                        <a-button type="default">
                            <a-icon type="star"></a-icon>
                        </a-button>
                    </div>
                </div>
                <div class="class-member-content">
                    <a-button-group>
                        <a-button>
                            <a-icon type="heat-map"></a-icon>ID: {{ opened_member_info.info.id }}
                        </a-button>
                        <a-button>
                            <a-icon type="flag"></a-icon> Joined on: {{ get_date(opened_member_info.info.date) }}
                        </a-button>
                    </a-button-group>
                    <br /><br />
                    <a-button-group>
                        <a-button>
                            <a-icon type="mail"></a-icon> Email: {{ opened_member_info.info.email }}
                        </a-button>
                        <a-button type="primary"><a :href="'mailto:'+opened_member_info.info.email">Mail To</a></a-button>
                    </a-button-group>
                    <br /><br />
                    <a-button-group>
                        <a-button>
                            <a-icon type="team"></a-icon> Joined {{ opened_member_info.info.class.split(',').length }} Class
                        </a-button>
                    </a-button-group>
                </div>
            </template>
        </a-spin>
    </div>



</div>
















</div>
<script type="text/javascript" src="../main/classes.js"></script>


<?php require 'pro_footer.php'; ?>