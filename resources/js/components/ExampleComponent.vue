<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Example Component</div>

                    <div class="card-body">
                        I'm an example component.
                        {{ message }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                message: 'Hullo'
            }
        },
        watch: {
            message: {
                deep: true,
                handler: function (tests) {
                    window.Echo.private('new-entity')
                        .listen('WebEntityProcessed', (e) => {
                            alert('got it');
                        });
                }
            }
        },
        mounted() {
            this.$echo.private('new-entity').listen('WebEntityProcessed', (payload) => {
                console.log(payload);
            });
            console.log('Component mounted.')
        },
    }
</script>
