// streamcss.runtime.js

export function applyStreamData(timing) {
    parseTag();
    getLiveData();
    setInterval(getLiveData, timing * 1000);
}

export function animElements(elements, animName, timing) {
    let index = 0;
    const parent = elements[0].parentNode;

    const setupStyles = (el, styles) => Object.assign(el.style, styles);

    const applyCommonStyles = (el) => {
        setupStyles(el, {
            display: "block",
            width: "100%",
            position: "absolute",
            transition: ".7s"
        });
        el.parentNode.style.position = "relative";
        el.parentNode.style.overflow = "hidden";
    };

    if (animName === "sliders") {
        elements.forEach(el => {
            applyCommonStyles(el);
            el.style.left = "100%";
        });

        parent.intervalAnime = setInterval(() => {
            elements.forEach(el => el.style.left = "100%");
            elements[index].style.left = "0";
            index = (index + 1) % elements.length;
        }, timing * 1000);

    } else if (animName === "fade") {
        elements.forEach(el => {
            applyCommonStyles(el);
            el.style.opacity = 0;
            el.style.background = "#336";
        });

        parent.intervalAnime = setInterval(() => {
            elements.forEach(el => el.style.opacity = 0);
            elements[index].style.opacity = 1;
            index = (index + 1) % elements.length;
        }, timing * 1000);
    }
}

function parseTag() {
    document.querySelectorAll("data-source").forEach(el => {
        const span = document.createElement("span");
        span.dataset.source = el.innerText;

        const format = el.getAttribute("format");
        const animate = el.getAttribute("animate");
        if (format) span.setAttribute("format", format);
        if (animate) span.setAttribute("animate", animate);

        el.replaceWith(span);
        formatAnimTag(); // Assumes function is defined elsewhere
    });
}

function setCounter(component) {
    let value = parseInt(component.getAttribute("initialValue"), 10) || 0;
    const incrKey = component.getAttribute("incr");
    const decrKey = component.getAttribute("decr");
    const display = document.createElement("span");
    display.innerText = value;

    document.addEventListener("keyup", (e) => {
        if (e.keyCode === 107 && incrKey === "+") value++;
        if (e.keyCode === 109 && decrKey === "-") value--;
        display.innerText = value;
    });

    component.appendChild(display);
}

function setSound(el, soundIndex) {
    if (el.propAnim[soundIndex] != undefined) {
        let snd = el.propAnim[soundIndex].split(' ');
        let audio = document.createElement("audio");
        audio.src = snd[0];

        if (snd[1] != undefined) {
            let during = snd[1].substring(0, snd[1].length - 1)
            console.log(during);
            setTimeout(() => {
                el.appendChild(audio)
                audio.play();
            }, during * 1000);
        } else {
            el.appendChild(audio)
            audio.play();
        }
    }
}
function launchAlertAnimation(el, during = 5) {
    // Annuler si déjà en animation
    if (el._alertRunning) return;

    el._alertRunning = true;

    const toClassList = cls => cls?.split(",").map(c => c.trim()).filter(Boolean) ?? [];

    const enterClasses = toClassList(el.propAnim.onEnter);
    const alertClasses = toClassList(el.propAnim.onAlert);
    const leaveClasses = toClassList(el.propAnim.onLeave);

    if (enterClasses.length) el.classList.add(...enterClasses);
    setSound(el, "soundEnter");


    const onEnterEnd = () => {
        el.removeEventListener("animationend", onEnterEnd);
        if (enterClasses.length) el.classList.remove(...enterClasses);

        if (alertClasses.length) el.classList.add(...alertClasses);
        setSound(el, "soundAlert");
        setTimeout(() => {
            if (alertClasses.length) el.classList.remove(...alertClasses);

            if (leaveClasses.length) {
                el.classList.add(...leaveClasses);

                el.addEventListener("animationend", function onLeaveEnd() {
                    el.removeEventListener("animationend", onLeaveEnd);
                    el._alertRunning = false;
                    setSound(el, "soundLeave");
                    // el.remove(); // ou el.style.display = "none";
                });
            } else {
                el._alertRunning = false;
                //  el.remove();
            }
        }, during * 1000);
    };

    el.addEventListener("animationend", onEnterEnd);
}




export function clipDiffuse(url, duration) {
    if (document.getElementById("clipDiffuse") != undefined) {
        return false;
    }
    const iframe = document.createElement("iframe");
    let del = document.createElement("span");
    del.classList.add("closeBtn");
    del.innerText = "X";
    iframe.id = "clipDiffuse";
    iframe.src = url;
    iframe.autoplay = true;
    del.onclick = function () {
        iframe.remove();
        this.remove();
    }
    //  console.dir(iframe);

    /* iframe.contentWindow.document.body.onclick = function () {
         alert("POP");
         //console.log(duration);
         let count=document.querySelector("[clip_displayer] span");
       //  let duration=count.innerText*1;
         let interv=setInterval(()=>{
 
             if(duration-1==0){
                 clearInterval(interv);
                 this.remove();
             }
             duration--;
             count.innerText=duration;
         },1000)
     }*/

    document.querySelector("[clip_displayer]").appendChild(iframe);
    setTimeout(() => {
        document.querySelector("[clip_displayer]").appendChild(del);
    }, duration * 1000);

}
function setChatDisplayer(chat) {
    let divs = chat.querySelectorAll("div");
    if (divs.length > 5) {
        divs[0].remove();
    }
}
export function applyStreamCSS(bot, styleSelector = 'style[type="streamcss"]') {
    const rules = {
        "stream-label": (el, val) => {
            const label = document.createElement("span");
            label.innerText = val;
            el.appendChild(label);
        },
        "stream-compoment": (el, val, props) => {
            const propMap = Object.fromEntries(props.map(p => p.split(":").map(s => s.trim())));

            switch (val) {
                case 'chat': {
                    if (document.querySelector(".chat h2") == undefined) {
                        let title = document.createElement("h2");
                        title.innerText = (propMap["chat-label"]) ? propMap["chat-label"] : "chat";
                        el.appendChild(title);
                    }
                    if (bot) {
                        bot.onMessage((tag, message) => {

                            setChatDisplayer(el);
                            let msgContainer = document.createElement("div");
                            msgContainer.style.display = 'flex';
                            msgContainer.innerHTML = `<span>${tag.username}</span><span>${message}</span>`;
                            el.appendChild(msgContainer);
                        })
                        if (propMap.hideCmd)
                            bot.setCommand(propMap.hideCmd, (args, tag, channel) => {
                                if (tag.username !== channel.substring(1)) {
                                    bot.message(`/me vous n'avez pas accès à cette commande`);
                                    return;
                                }
                                el.style.display = "none";
                            });
                        if (propMap.displayCmd)
                            bot.setCommand(propMap.displayCmd, (args, tag, channel) => {
                                if (tag.username !== channel.substring(1)) {
                                    bot.message(`/me vous n'avez pas accès à cette commande`);
                                    return;
                                }
                                el.style.display = "flex";
                            });
                    }
                    /*  _cBot.on('message', (channel, tags, message, self) => {
                          console.dir(el);
                          if (self) return; // Ignore les messages du bot
                          
                      });*/
                    break;
                }
                case "widget":
                    if (propMap.showCompoment && propMap.showCompoment !== "onload") {
                        el.style.display = "none";

                        if (propMap.showCompoment === "oncmd" && propMap.displayCmd) {
                            if (bot)
                                bot.setCommand(propMap.displayCmd, (args, tag, channel) => {
                                    if (tag.username !== channel.substring(1)) {
                                        bot.message(`/me vous n'avez pas accès à cette commande`);
                                        return;
                                    }
                                    el.style.display = "block";
                                });
                        }

                        if (propMap.showCompoment === "oncmd" && propMap.hideCmd) {
                            if (bot)
                                bot.setCommand(propMap.hideCmd, (args, tag, channel) => {
                                    if (tag.username !== channel.substring(1)) {
                                        bot.message(`/me vous n'avez pas accès à cette commande`);
                                        return;
                                    }
                                    el.style.display = "none";
                                });
                        }
                    }
                    break;

                case "alert":
                    Object.assign(el.style, {
                        width: "25%",
                        aspectRatio: "16/9",
                        margin: "0 auto"
                    });
                    const during = parseInt(propMap.duration) || 5;
                    const sounds = propMap.sound;
                    console.dir(propMap);
                    el.triggerEvent = false; // ne se lance pas automatiquement
                    el._alertRunning = false;
                    el.propAnim = propMap;
                    // Prépare la fonction trigger manuelle
                    el.trigger = () => {
                        if (!el._alertRunning) {
                            launchAlertAnimation(el, during);
                        }
                    };

                    break;

                case "clip":
                    Object.assign(el.style, {
                        width: "360px",
                        aspectRatio: "16/9"
                    });
                    el.setAttribute("clip_displayer", "1");
                    break;

                case "counter":
                    el.style.width = "15%";
                    el.style.aspectRatio = "16/9";
                    setCounter(el);
                    break;
            }
        },
        "stream-default": (el, val) => {
            if (val === "true") {
                Object.assign(el.style, {
                    margin: "0",
                    padding: "0",
                    overflow: "hidden",
                    width: "100%",
                    height: "60px",
                    fontFamily: "Calibri"
                });
            }
        }, "stream-overlay": (el, val) => {
            if (val === "true") {
                Object.assign(el.style, {
                    margin: "0",
                    padding: "0",
                    overflow: "hidden",
                    width: "100%",
                    height: "100%",
                    fontFamily: "Calibri"
                });
            }
        },
        "stream-height": (el, val) => {
            el.style.height = val;
        },
        "fix-areas": (el, val) => {
            let params = new URLSearchParams(window.location.search);
            let sceneName = params.get('scene');
            let cssParams = val.split("-");
            let sceneParam = cssParams[0];
            if (sceneName == sceneParam) {
                Object.assign(el.style, {
                    gridTemplateAreas: `${cssParams[1]}`
                });
                let areas = cssParams[1]
                    .replace(/"/g, "")       // enlève tous les guillemets
                    .split(/\s+/)            // split par espaces
                    .filter(a => a.trim() !== ""); // enlève les vides
                document.querySelectorAll("[g_area]").forEach(el => {
                    let a = el.getAttribute("g_area");
                    if (!areas.includes(a)) {
                        el.style.display = "none";
                    }
                });
            }

        },
        "fix-grid": (el, val) => {
            console.log(val);
            Object.assign(el.style, {
                display: "grid",
                gridTemplateAreas: `${val}`
            });
            el.style.gridTemplateAreas = `${val}`;
            document.querySelectorAll("[g_area]").forEach(el => {
                el.style["grid-area"] = el.getAttribute("g_area");
            });
        },
        "fix-flex": (el, val) => {
            const [direction, justify] = val.split(" ");
            Object.assign(el.style, {
                display: "flex",
                flexDirection: direction,
                justifyContent: justify
            });
        },
        "fix-overflow": (el, val) => {
            if (val === "true") {
                Object.assign(el.style, {
                    overflow: "hidden",
                    width: "100%",
                    height: "60px"
                });
            }
        },
        "clock-format": (el) => {
            const updateTime = () => {
                const d = new Date();
                el.textContent = `${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}`;
            };
            updateTime();
            el._clockInterval = setInterval(updateTime, 1000);
        },
        "auto-refresh": (el, val) => {
            const delay = parseInt(val);
            if (!el._autoRefresh) {
                el._autoRefresh = setInterval(() => {
                    el.dispatchEvent(new CustomEvent("refresh"));
                }, delay);
            }
        }
    };

    const parseRules = () => {
        const link = document.createElement("link");
        link.href = "streamcss.runtime.css";
        link.rel = "stylesheet";
        document.head.appendChild(link);

        const styleTag = document.querySelector(styleSelector);
        if (!styleTag) return;

        const raw = styleTag.innerHTML;
        const blocks = raw.split("}").map(b => b.trim()).filter(Boolean);

        blocks.forEach(block => {
            const [selector, propString] = block.split("{").map(x => x.trim());
            const elements = document.querySelectorAll(selector);
            if (!elements.length) return;

            const props = propString.split(";").map(p => p.trim()).filter(Boolean);
            props.forEach(rule => {
                const [key, value] = rule.split(":").map(x => x.trim());
                if (rules[key]) {
                    elements.forEach(el => rules[key](el, value, props));
                }
            });
        });
    };

    document.addEventListener("DOMContentLoaded", parseRules);
}
