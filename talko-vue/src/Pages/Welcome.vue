<template>
    <div>
        <div class="bg-[#38385f]">
            <header>
                <div :class="{ 'mx-2 pb-5': open, 'hidden': !open }">
                    <div class="flex items-center justify-between py-5 md:block">
                        <a >
                            <img
                                src="#"
                                class="w-12 h-12 cursor-pointer rounded-full border-2 border-white mt-0"
                                alt="Chat Logo"
                            />
                        </a>
                        <div class="md:hidden">
                            <button
                                class="menu-btn text-gray-400 hover:text-gray-300"
                                @click="open = !open"
                            >
                                <template v-if="open">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </template>
                                <template v-else>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="w-6 h-6"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                                        />
                                    </svg>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
                <nav :class="{ 'pb-5 md:text-sm absolute z-20 top-0 inset-x-0 bg-[#38385f] rounded-xl mx-2 mt-2 md:mx-0 md:mt-0 md:relative md:bg-transparent': open, '' : !open }">
                    <div class="gap-x-14 items-center max-w-screen-xl mx-auto px-4 md:flex md:px-8">
                        <div class="flex items-center justify-between py-5 md:block">
                            <a >
                                <img
                                    src="#"
                                    class="w-16 h-16 cursor-pointer rounded-full border-2 border-white mt-0"
                                    alt="Chat Logo"
                                />
                            </a>
                            <div class="md:hidden">
                                <button
                                    class="menu-btn text-gray-400 hover:text-gray-300"
                                    @click="open = !open"
                                >
                                    <template v-if="open">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </template>
                                    <template v-else>
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="w-6 h-6"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                                            />
                                        </svg>
                                    </template>
                                </button>
                            </div>
                        </div>
                        <div
                            :class="{ 'block' : open, 'hidden' : !open }"
                            class="flex-1 items-center mt-8 md:mt-0 md:flex"
                        >
                            <ul
                                class="flex-1 justify-end items-center space-y-6 md:flex md:space-x-6 md:space-y-0"
                            >
                                <li class="text-gray-300 hover:text-gray-400">
                                    <a  class="block">How It Works</a>
                                </li>
                                <li class="text-gray-300 hover:text-gray-400">
                                    <a  class="block">Features</a>
                                </li>
                                <li class="text-gray-300 hover:text-gray-400">
                                    <a class="block">Support</a>
                                </li>
                                <li>
                                    <Link
                                        class="flex items-center justify-center gap-x-1 py-2 px-4 text-white font-medium bg-sky-500 hover:bg-sky-400 active:bg-sky-600 duration-150 rounded-full md:inline-flex">
                                        Start Chatting
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            class="w-5 h-5"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>
            <section class="relative">
                <div class="relative z-10 max-w-screen-xl mx-auto px-4 py-28 md:px-8">
                    <div class="space-y-5 max-w-4xl mx-auto text-center">
                        <h2
                            class="text-2xl text-white font-extrabold mx-auto md:text-4xl"
                        >
                            Connect Instantly with People Around You"
                        </h2>
                        <p class="max-w-2xl mx-auto text-gray-400">
                            Join our chat platform to connect with others effortlessly! Whether you're looking to engage in group conversations, chat one-on-one, or connect with users sharing the same IP network, our system makes communication seamless and instant.
                        </p>
                        <form
                            @submit.prevent
                            class="justify-center items-center gap-x-3 sm:flex"
                        >
                            <Link>
                                <button
                                    class="flex items-center justify-center gap-x-2 py-2.5 px-4 mt-3 w-full text-sm text-white font-medium bg-sky-500 hover:bg-sky-400 active:bg-sky-600 duration-150 rounded-lg sm:mt-0 sm:w-auto"
                                >
                                    Start Chatting
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        class="w-5 h-5"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M2 10a.75.75 0 01.75-.75h12.59l-2.1-1.95a.75.75 0 111.02-1.1l3.5 3.25a.75.75 0 010 1.1l-3.5 3.25a.75.75 0 11-1.02-1.1l2.1-1.95H2.75A.75.75 0 012 10z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </Link>
                        </form>
                        <div
                            class="flex justify-center items-center gap-x-4 text-gray-400 text-sm"
                        >
                            <div class="flex">
                                <svg
                                    class="w-5 h-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753 .381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                    />
                                </svg>
                                <svg
                                    class="w-5 h-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753 .381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                    />
                                </svg>
                                <svg
                                    class="w-5 h-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753 .381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                    />
                                </svg>
                                <svg
                                    class="w-5 h-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753 .381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                    />
                                </svg>
                            </div>
                            <p>Thousands of satisfied users</p>
                        </div>
                    </div>
                </div>
                <div
                    class="absolute inset-0 m-auto max-w-xs h-[357px] blur-[118px] sm:max-w-md md:max-w-lg"
                    style="
            background: linear-gradient(
              106.89deg,
              rgba(192, 132, 252, 0.11) 15.73%,
              rgba(14, 165, 233, 0.41) 15.74%,
              rgba(232, 121, 249, 0.26) 56.49%,
              rgba(79, 70, 229, 0.4) 115.91%
            );
          "
                ></div>
            </section>
            <section class="relative bg-[#2d2d4f] py-20">
                <div class="max-w-screen-xl mx-auto px-4 md:px-8">
                    <div class="text-center">
                        <h2 class="text-3xl text-white font-bold mb-6">Key Features of Our Chat</h2>
                        <p class="text-gray-400 max-w-2xl mx-auto mb-12">
                            Our chat is loaded with features designed to make your communication as smooth and efficient as possible. Check out some of our key features below!
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Feature 1 -->
                        <div class="bg-[#38385f] rounded-lg p-8 text-center shadow-lg">
                            <div class="flex justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-2.21 1.79-4 4-4s4 1.79 4 4v1h2.5A1.5 1.5 0 0124 13.5v7A1.5 1.5 0 0122.5 22h-17A1.5 1.5 0 014 20.5v-7A1.5 1.5 0 015.5 12H8v-1c0-2.21 1.79-4 4-4s4 1.79 4 4v1h-4v-1z" />
                                </svg>
                            </div>
                            <h4 class="text-lg text-white font-semibold mb-2">End-to-End Encryption</h4>
                            <p class="text-gray-400">
                                All your messages are secure with our end-to-end encryption, ensuring privacy and safety.
                            </p>
                        </div>
                        <!-- Feature 2 -->
                        <div class="bg-[#38385f] rounded-lg p-8 text-center shadow-lg">
                            <div class="flex justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v4H4V4zm0 6h16v10H4V10z" />
                                </svg>
                            </div>
                            <h4 class="text-lg text-white font-semibold mb-2">Seamless File Sharing</h4>
                            <p class="text-gray-400">
                                Easily share images, videos, documents, and more directly through the chat with no hassles.
                            </p>
                        </div>
                        <!-- Feature 3 -->
                        <div class="bg-[#38385f] rounded-lg p-8 text-center shadow-lg">
                            <div class="flex justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 19.364a1.5 1.5 0 010-2.121l1.415-1.414a1.5 1.5 0 012.122 0l7.071 7.071a1.5 1.5 0 01-2.121 2.121L5.12 19.364z" />
                                </svg>
                            </div>
                            <h4 class="text-lg text-white font-semibold mb-2">Customizable Themes</h4>
                            <p class="text-gray-400">
                                Personalize your chat experience with our customizable themes, offering both dark and light modes.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <section class="relative bg-[#1f1f3a] py-20">
                <div class="max-w-screen-xl mx-auto px-4 md:px-8">
                    <div class="text-center">
                        <h2 class="text-3xl text-white font-bold mb-6">Dashboard Preview</h2>
                        <p class="text-gray-400 max-w-2xl mx-auto mb-12">
                            Experience our intuitive chat dashboard that keeps you connected and organized. See what makes our user interface stand out from the rest.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 ">
                        <!-- Dashboard Feature 1 -->
                        <div class="bg-[#38385f] rounded-lg p-8 shadow-lg">
                            <img src="#" alt="Dashboard Overview" class="rounded-md mb-6">
                            <h4 class="text-lg text-white font-semibold mb-2">Comprehensive Overview</h4>
                            <p class="text-gray-400">
                                Get a quick snapshot of all your conversations, contacts, and recent activity, all in one place.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import {Link} from "@inertiajs/vue3";

export default {
    components: {
        Link,
    },

    data() {
        return {
            open: false,
        };
    },
};
</script>
